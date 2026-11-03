<?php declare(strict_types=1);
// Presentation only: extract existing sentences without rewriting stored data.
$sections = ['description' => [], 'technical' => [], 'usage' => []];
$rawText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($product['long_description'] ?? ''))) ?? '');
$seen = [];
foreach (preg_split('/(?<=[.!?])\s+|\r?\n/u', $rawText, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $sentence) {
    $sentence = trim($sentence);
    if (preg_match('/firmamız|hemen bizimle|iletişime geç|sipariş vermek|tercih edin|siz de|profesyonel ekibimiz|neden biz|en iyi hizmet/iu', $sentence)) {
        continue;
    }
    $sentence = preg_replace('/^.*?(?:Nedir\?|Nelerdir\?)\s*/u', '', $sentence) ?? $sentence;
    $sentence = preg_replace('/^(?:[A-ZÇĞİÖŞÜ0-9]+\s+){2,}(?=[A-ZÇĞİÖŞÜ][a-zçğıöşü])/u', '', $sentence) ?? $sentence;
    $sentence = preg_replace('/^\d+\.\s*/u', '', $sentence) ?? $sentence;
    if ($sentence === '' || preg_match('/^\d+\.?$|^İşte .*alanlar:$/iu', $sentence)) { continue; }
    $key = mb_strtolower(trim($sentence, " .!?"));
    if (isset($seen[$key])) { continue; }
    $seen[$key] = true;
    if (preg_match('/kullanım alan|sektör|endüstri|depolama|nakliye|taşımacılık|ofis|restoran|evler:|boyama ve dekorasyon|oto bakım|sanat ve el|e-ticaret|perakende|promosyon|inşaat/iu', $sentence)) {
        $sections['usage'][] = $sentence;
    } elseif (preg_match('/yapışkan|tutkal|reçine|hotmelt|dayanık|sıcaklık|ölçü|genişlik|uzunluk|malzeme|geri dönüştürü|kesilebilir|iz bırak|çıkarılabilir/iu', $sentence)) {
        $sections['technical'][] = $sentence;
    } else {
        $sections['description'][] = $sentence;
    }
}
$featureTexts = [];
foreach (($specs ?? []) as $pair) {
    $featureTexts[] = trim($pair['label'] . ': ' . $pair['value'], ': ');
}
if ($featureTexts === []) {
    foreach ($sections['technical'] as $sentence) {
        // Short source clauses are used intact; no generic selling claims.
        foreach (preg_split('/,\s*/u', $sentence) ?: [] as $clause) {
            $clause = trim($clause, " .");
            if (mb_strlen($clause) >= 8 && mb_strlen($clause) <= 110) {
                $featureTexts[] = $clause;
            }
        }
    }
}
$featureTexts = array_slice(array_values(array_unique($featureTexts)), 0, 4);
$shortText = trim((string) ($product['short_description'] ?? ''));
if ($shortText === '') {
    $shortText = $sections['description'][0] ?? $sections['technical'][0] ?? $sections['usage'][0] ?? '';
}
if ($sections['description'] === [] && $sections['technical'] !== []) {
    $sections['description'][] = array_shift($sections['technical']);
}
