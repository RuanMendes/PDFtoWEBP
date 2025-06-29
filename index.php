<?php
$pdfDir = 'pdfs/';
$outputDir = 'img/';
$ghostscript = '"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe"';
$cwebp = 'cwebp.exe'; // Certifique-se de que o binário do cwebp está no PATH ou na mesma pasta

$pdfFiles = glob($pdfDir . "*.pdf");

if (empty($pdfFiles)) {
    echo "❌ Nenhum arquivo PDF encontrado na pasta.<br>";
    exit;
}

foreach ($pdfFiles as $pdfFile) {
    $pdfFileName = pathinfo($pdfFile, PATHINFO_FILENAME);
    $inputPath = '"' . $pdfFile . '"';
    $outputPngPath = '"' . $outputDir . $pdfFileName . '_%d.png"';

    // Gerar PNG com Ghostscript
    $cmd = "$ghostscript -dNOSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r300 -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -dUseCIEColor -sOutputFile=$outputPngPath $inputPath";
    echo "🔄 Processando: $pdfFileName.pdf<br>";
    exec($cmd . ' 2>&1', $saida, $codigoRetorno);
    echo "✅ Código de Retorno: $codigoRetorno<br>";

    // Converter os PNGs gerados para WebP
    $generatedFiles = glob($outputDir . $pdfFileName . '_*.png');
    foreach ($generatedFiles as $imageFile) {
        $webpPath = preg_replace('/\.png$/', '.webp', $imageFile);
        $cmdWebp = "$cwebp -q 75 " . escapeshellarg($imageFile) . " -o " . escapeshellarg($webpPath);
        exec($cmdWebp . ' 2>&1', $saidaWebp, $retWebp);
        if ($retWebp === 0) {
            echo "🟢 Gerado WebP: " . basename($webpPath) . "<br>";
            unlink($imageFile); // Apaga o PNG intermediário
        } else {
            echo "⚠️ Falha ao converter para WebP: " . basename($imageFile) . "<br>";
        }
    }

    // Renomear a primeira página (se quiser manter nome simples sem "_1")
    $firstWebp = $outputDir . $pdfFileName . '_1.webp';
    $targetWebp = $outputDir . $pdfFileName . '.webp';
    if (file_exists($firstWebp)) {
        rename($firstWebp, $targetWebp);
        echo "📄 Página principal renomeada para: " . basename($targetWebp) . "<br><br>";
    }
}
?>