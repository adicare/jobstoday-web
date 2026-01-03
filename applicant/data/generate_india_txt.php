<?php
// =======================================
// Generate CLEAN india.txt from in.txt
// (WITH VILLAGE FIELD)
// =======================================

set_time_limit(0);
ini_set('memory_limit', '1024M');

$inputFile  = __DIR__ . '/in.txt';
$outputFile = __DIR__ . '/india.txt';

$in  = fopen($inputFile, 'r');
$out = fopen($outputFile, 'w');

if (!$in || !$out) {
    die("❌ File open error");
}

$written = 0;
$skipped = 0;

while (($line = fgets($in)) !== false) {

    $line = trim($line);
    if ($line === '') {
        $skipped++;
        continue;
    }

    // split by whitespace (GeoNames India is space-based)
    $cols = preg_split('/\s+/', $line);

    // must have minimum columns
    if (count($cols) < 8) {
        $skipped++;
        continue;
    }

    $country = $cols[0];
    if ($country !== 'IN') {
        $skipped++;
        continue;
    }

    // village / place name
    $village = $cols[2] ?? null;

    // lat & lng are ALWAYS near the end
    $lat = $cols[count($cols) - 3] ?? null;
    $lng = $cols[count($cols) - 2] ?? null;

    if (!is_numeric($lat) || !is_numeric($lng)) {
        $skipped++;
        continue;
    }

    // pincode must be numeric (5 or 6 digits)
    $pincode = null;
    if (isset($cols[1]) && preg_match('/^\d{5,6}$/', $cols[1])) {
        $pincode = $cols[1];
    }

    // state & district (skip numeric admin codes)
    $state    = $cols[3] ?? null;
    $district = $cols[5] ?? null;

    if (is_numeric($state) || is_numeric($district)) {
        $skipped++;
        continue;
    }

    // tehsil / mandal (best effort)
    $tehsil = $cols[6] ?? null;
    if (is_numeric($tehsil)) {
        $tehsil = null;
    }

    // write CLEAN tab-separated line (WITH VILLAGE)
    fwrite(
        $out,
        implode("\t", [
            'IN',
            $state,
            $district,
            $tehsil,
            $village,
            $pincode,
            $lat,
            $lng
        ]) . PHP_EOL
    );

    $written++;
}

fclose($in);
fclose($out);

echo "✅ india.txt regenerated successfully<br>";
echo "Written: {$written}<br>";
echo "Skipped: {$skipped}<br>";
