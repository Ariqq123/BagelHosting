<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Support\Facades\Http;

class ArixController extends AbstractLoginController
{
    public function index(): object
    {

        $endpoint = 'https://api.arix.gg/resource/arix-pterodactyl/verify';
    
        $response = Http::asForm()->post($endpoint, [
            'license' => 'ARIX-CHECK',
        ]);
    
        $responseData = $response->json();
    
        if (!$responseData['success']) {
            return response()->json([
                'status' => 'Not available'
            ]);
        }

        return response()->json([
            'NONCE' => '6af03b1c2edaa7a3615f4a8ca4855839',
            'ID' => '616942',
            'USERNAME' => 'yuvraj_hun_007',
            'TIMESTAMP' => '1751282130'
        ]);
    }
}