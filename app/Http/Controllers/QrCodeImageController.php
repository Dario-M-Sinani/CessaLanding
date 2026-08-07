<?php

namespace App\Http\Controllers;

use App\Models\QrCode as QrCodeModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class QrCodeImageController extends Controller
{
    public function show(Request $request, QrCodeModel $qrCode): Response
    {
        abort_unless(auth()->check(), 403);

        $result = (new Builder())->build(
            writer: new PngWriter(),
            data: $qrCode->content,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 16,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            logoPath: public_path('img/cessa_logo.png'),
            logoResizeToWidth: 150,
            logoPunchoutBackground: true,
        );

        $response = response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType());

        if ($request->boolean('download')) {
            $filename = Str::slug($qrCode->title) ?: 'qr-cessa';
            $response->header('Content-Disposition', "attachment; filename=\"{$filename}.png\"");
        }

        return $response;
    }
}
