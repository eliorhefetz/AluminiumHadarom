<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/src/Config/bootstrap.php';

use App\Database\Database;

function respond(array $payload, int $code = 200): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function failInternal(string $logMessage, int $code = 500, string $publicMessage = 'Internal server error'): void
{
    error_log($logMessage);

    respond([
        'status' => 'error',
        'message' => $publicMessage,
    ], $code);
}

function normalizeText(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return $text;
}

function requestGoogleTranslations(string $apiKey, string $sourceLang, string $targetLang, array $texts): array
{
    $url = 'https://translation.googleapis.com/language/translate/v2?key=' . urlencode($apiKey);

    $postParts = [
        'source=' . urlencode($sourceLang),
        'target=' . urlencode($targetLang),
        'format=text',
    ];

    foreach ($texts as $text) {
        $postParts[] = 'q=' . urlencode($text);
    }

    $postBody = implode('&', $postParts);

    $ch = curl_init($url);
    if ($ch === false) {
        failInternal('Failed to initialize curl', 500, 'Internal server error');
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postBody,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($curlError !== '') {
        failInternal('Translate cURL error: ' . $curlError, 502, 'Translation service is temporarily unavailable');
    }

    $result = json_decode((string) $response, true);

    if ($httpCode !== 200) {
        $encodedResponse = is_array($result)
            ? json_encode($result, JSON_UNESCAPED_UNICODE)
            : (string) $response;

        failInternal(
            'Google Translate request failed. HTTP ' . $httpCode . ' Response: ' . $encodedResponse,
            502,
            'Translation service is temporarily unavailable'
        );
    }

    $translations = $result['data']['translations'] ?? null;

    if (!is_array($translations)) {
        $encodedResponse = is_array($result)
            ? json_encode($result, JSON_UNESCAPED_UNICODE)
            : (string) $response;

        failInternal(
            'Invalid Google Translate response: ' . $encodedResponse,
            502,
            'Translation service returned an invalid response'
        );
    }

    $output = [];
    foreach ($translations as $item) {
        $translated = $item['translatedText'] ?? '';
        $output[] = html_entity_decode((string) $translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return $output;
}

try {
    /** @var PDO $pdo */
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    failInternal('DB connection failed: ' . $e->getMessage(), 500, 'Internal server error');
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond([
        'status' => 'error',
        'message' => 'Method not allowed',
    ], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);

if (!$data || !is_array($data)) {
    respond([
        'status' => 'error',
        'message' => 'Invalid JSON',
    ], 400);
}

$texts = $data['texts'] ?? null;
$sourceLang = trim((string) ($data['source'] ?? 'he'));
$targetLang = trim((string) ($data['target'] ?? 'ru'));

$allowedLangs = ['he', 'ru', 'en'];

if (!in_array($sourceLang, $allowedLangs, true) || !in_array($targetLang, $allowedLangs, true)) {
    respond([
        'status' => 'error',
        'message' => 'Unsupported language',
    ], 400);
}

if (!is_array($texts) || $texts === []) {
    respond([
        'status' => 'error',
        'message' => 'Missing texts',
    ], 400);
}

$normalizedTexts = [];
foreach ($texts as $text) {
    if (!is_string($text)) {
        continue;
    }

    $text = normalizeText($text);
    if ($text === '') {
        continue;
    }

    $normalizedTexts[] = $text;
}

if ($normalizedTexts === []) {
    respond([
        'status' => 'error',
        'message' => 'No valid texts',
    ], 400);
}

$apiKey = $_ENV['GOOGLE_TRANSLATE_API_KEY'] ?? '';
if ($apiKey === '') {
    failInternal('GOOGLE_TRANSLATE_API_KEY is missing', 500, 'Internal server error');
}

try {
    $uniqueTexts = array_values(array_unique($normalizedTexts));
    $cachedMap = [];

    if ($uniqueTexts !== []) {
        $placeholders = implode(',', array_fill(0, count($uniqueTexts), '?'));

        $stmt = $pdo->prepare("
            SELECT source_text, translated_text
            FROM translations
            WHERE source_lang = ?
              AND target_lang = ?
              AND source_text IN ($placeholders)
        ");

        $stmt->execute(array_merge([$sourceLang, $targetLang], $uniqueTexts));

        foreach ($stmt->fetchAll() as $row) {
            $cachedMap[$row['source_text']] = $row['translated_text'];
        }
    }

    $missingTexts = [];
    foreach ($uniqueTexts as $text) {
        if (!array_key_exists($text, $cachedMap)) {
            $missingTexts[] = $text;
        }
    }

    if ($missingTexts !== []) {
        $googleTranslations = requestGoogleTranslations($apiKey, $sourceLang, $targetLang, $missingTexts);

        $insertStmt = $pdo->prepare("
            INSERT INTO translations (source_lang, target_lang, source_text, translated_text)
            VALUES (:source_lang, :target_lang, :source_text, :translated_text)
            ON DUPLICATE KEY UPDATE translated_text = VALUES(translated_text)
        ");

        foreach ($missingTexts as $index => $sourceText) {
            $translatedText = $googleTranslations[$index] ?? $sourceText;

            $insertStmt->execute([
                ':source_lang' => $sourceLang,
                ':target_lang' => $targetLang,
                ':source_text' => $sourceText,
                ':translated_text' => $translatedText,
            ]);

            $cachedMap[$sourceText] = $translatedText;
        }
    }

    $finalTranslations = [];
    foreach ($normalizedTexts as $text) {
        $finalTranslations[] = $cachedMap[$text] ?? $text;
    }

    respond([
        'status' => 'success',
        'translations' => $finalTranslations,
        'cached' => count($missingTexts) === 0,
    ]);
} catch (Throwable $e) {
    failInternal('Translation endpoint error: ' . $e->getMessage(), 500, 'Internal server error');
}