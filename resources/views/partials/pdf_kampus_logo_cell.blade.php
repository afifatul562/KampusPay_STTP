{{-- Sel untuk kop surat PDF (DomPDF): logo dari disk/base64/URL — sama seperti kwitansi. --}}
@php
    $tdClass = $tdClass ?? 'logo-cell';
    $logoKampusSrc = '';
    $chrootNorm = realpath(base_path());
    $chrootNorm = $chrootNorm !== false ? str_replace('\\', '/', $chrootNorm) : '';
    $logoRelCandidates = [
        'images/logo_kampus.png',
        'images/logo_sttpayakumbuh.png',
        'images/logo.png',
    ];
    foreach ($logoRelCandidates as $rel) {
        $full = public_path($rel);
        if (! is_file($full)) {
            continue;
        }
        $raw = @file_get_contents($full);
        if ($raw === false || $raw === '') {
            continue;
        }
        $mime = 'image/png';
        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($full);
            if (is_string($detected) && str_starts_with($detected, 'image/')) {
                $mime = $detected;
            }
        }
        $embed = '';
        $tooLarge = strlen($raw) > 400 * 1024;
        if (function_exists('imagecreatefromstring') && function_exists('imagepng')) {
            $img = @imagecreatefromstring($raw);
            if ($img !== false) {
                $w = imagesx($img);
                $h = imagesy($img);
                $maxW = 280;
                if ($w > $maxW || $tooLarge) {
                    $newW = min($w, $maxW);
                    $newH = max(1, (int) round($h * ($newW / $w)));
                    $resized = imagecreatetruecolor($newW, $newH);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                    imagefill($resized, 0, 0, $transparent);
                    imagealphablending($resized, true);
                    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
                    imagesavealpha($resized, true);
                    ob_start();
                    imagepng($resized, null, 6);
                    $pngOut = ob_get_clean();
                    imagedestroy($resized);
                    imagedestroy($img);
                    if (is_string($pngOut) && $pngOut !== '') {
                        $embed = 'data:image/png;base64,' . base64_encode($pngOut);
                    }
                } else {
                    imagedestroy($img);
                }
            }
        }
        if ($embed !== '') {
            $logoKampusSrc = $embed;
            break;
        }
        $resolved = realpath($full);
        if ($resolved !== false && $chrootNorm !== '') {
            $resolvedNorm = str_replace('\\', '/', $resolved);
            if (str_starts_with($resolvedNorm, $chrootNorm)) {
                $logoKampusSrc = $resolvedNorm;
                break;
            }
        }
        if (strlen($raw) > 350 * 1024) {
            $logoKampusSrc = url($rel);
            break;
        }
        $logoKampusSrc = 'data:' . $mime . ';base64,' . base64_encode($raw);
        break;
    }
    if ($logoKampusSrc === '') {
        foreach ($logoRelCandidates as $rel) {
            $p = public_path($rel);
            if (! is_file($p)) {
                continue;
            }
            $resolved = realpath($p);
            if ($resolved !== false && $chrootNorm !== '') {
                $resolvedNorm = str_replace('\\', '/', $resolved);
                if (str_starts_with($resolvedNorm, $chrootNorm)) {
                    $logoKampusSrc = $resolvedNorm;
                    break;
                }
            }
            $logoKampusSrc = url($rel);
            break;
        }
    }
@endphp
<td class="{{ $tdClass }}"@if (!empty($tdStyle ?? null)) style="{{ $tdStyle }}"@endif>
    @if ($logoKampusSrc !== '')
        <img src="{{ $logoKampusSrc }}" alt="" class="logo">
    @else
        <span style="font-size: 9px; color: #666;">Logo</span>
    @endif
</td>
