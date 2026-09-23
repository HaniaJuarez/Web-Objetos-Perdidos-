<?php

require_once 'config.php';

$url = SUPABASE_URL . '/rest/v1/usuarios?select=id&limit=1';

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . SUPABASE_KEY,
    'Authorization: Bearer ' . SUPABASE_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "Código HTTP: " . $httpCode . "<br>";
echo "Respuesta de Supabase:<br>";
echo "<pre>";
echo htmlspecialchars($response);
echo "</pre>";

?>