<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Illuminate\Support\Facades\Log;

class LLMController extends Controller
{
    public function analyze(Request $request)
    {
        $prompt = $request->input('prompt');

        if (!$prompt) {
            return response()->json([
                'error' => 'Geçersiz veri aktarımı. Lütfen fırtına verilerinin seçili olduğundan emin olun.'
            ], 400);
        }

        try {
            // .env içerisindeki AWS kimliklerine (ve varsa Session Token'a) bağlanır
            $credentials = [
                'key'    => env('AWS_BEDROCK_KEY', ''),
                'secret' => env('AWS_BEDROCK_SECRET', '')
            ];
            
            // Eğer geçici STS hüviyeti (veya Bearer/Session Token) alınmışsa:
            if (!empty(env('AWS_BEDROCK_TOKEN'))) {
                $credentials['token'] = env('AWS_BEDROCK_TOKEN');
            }

            $client = new BedrockRuntimeClient([
                'region' => env('AWS_BEDROCK_REGION', 'us-east-1'),
                'version' => 'latest',
                'credentials' => $credentials
            ]);

            // Llama 3 Instruct özel format - bu format olmadan prompt echo edilir
            $systemPrompt = "Sen bir uzay hava durumu ve güneş fırtınaları konusunda uzman bir bilim insanısın. Sana verilen güneş fırtınası verilerini analiz edeceksin. Analizini şu sırayla yap:

1. FIRTINA VERİLERİ ANALİZİ: Önce verilen fırtına parametrelerini (DST indeksi, Kp değeri, X-ray akısı, proton akısı vb.) detaylı şekilde incele ve yorumla. TAMAMEN Türkçe olsun.

2. MUHTEMEL ETKİLER: Bu verilere dayanarak fırtınanın muhtemel yaratacağı etkileri açıkla (uydu haberleşmesi, GPS sistemleri, elektrik şebekeleri, havacılık, radyo iletişimi, kutup kuşakları vb. üzerindeki etkiler). Teknik terimler kullan.

3. EN ÇOK ETKİLENECEK BÖLGELER: Bu bölümün başlığını <h3 style=\"color: #f97316; font-weight: bold;\">🌍 EN ÇOK ETKİLENECEK BÖLGELER</h3> şeklinde turuncu renkte yaz. Fırtınanın dünyanın hangi bölgelerini en çok etkileyeceğini belirt (yüksek enlemler, kutup bölgeleri, belirli kıtalar vb.).

4. FIRTINADAN ÖNCE YAPILMASI GEREKENLER: Fırtına öncesi alınması gereken önlemleri listele (yedekleme, koruyucu tedbirler, uyarı sistemleri vb.).

5. FIRTINADAN SONRA YAPILMASI GEREKENLER: Fırtına sonrası yapılması gereken işlemleri açıkla (sistem kontrolleri, hasar tespiti, normale dönüş prosedürleri vb.).

Sadece geçerli HTML kodu döndür, açıklama yapma. Diğer başlıklar için <h3>, listeler için <ul><li> ve paragraflar için <p> kullan.";
            
            $userMessage = $prompt . "\n\nYukarıdaki verileri analiz et ve belirtilen 5 bölümü içeren detaylı bir rapor hazırla. Sadece geçerli HTML kodlarını döndür. Başka açıklama ekleme.";
            
            // Llama 3 Instruct özel chat template
            $formattedPrompt = "<|begin_of_text|><|start_header_id|>system<|end_header_id|>\n\n{$systemPrompt}<|eot_id|><|start_header_id|>user<|end_header_id|>\n\n{$userMessage}<|eot_id|><|start_header_id|>assistant<|end_header_id|>\n\n";
            
            $body = json_encode([
                "prompt" => $formattedPrompt,
                "max_gen_len" => 2048,
                "temperature" => 0.4,
                "top_p" => 0.9
            ]);

            // Model ID: Meta Llama 3 70B Instruct
            $modelId = "meta.llama3-70b-instruct-v1:0";

            $result = $client->invokeModel([
                'body' => $body,
                'modelId' => $modelId,
                'accept' => 'application/json',
                'contentType' => 'application/json',
            ]);

            $responseBody = json_decode($result->get('body')->getContents(), true);
            
            // Llama 3 response formatı — generation alanında döner
            $aiResponseHTML = $responseBody['generation'] ?? '';
            
            // Olası stop token'ları temizle
            $aiResponseHTML = str_replace(['<|eot_id|>', '<|end_of_text|>'], '', $aiResponseHTML);
            $aiResponseHTML = trim($aiResponseHTML);

            return response()->json([
                'success' => true,
                'html' => $aiResponseHTML
            ]);

        } catch (\Throwable $e) {
            // Sınıf Bulunamadı (SDK yok) veya AWS SDK Hatalarını yakala
            Log::error("AWS Bedrock AI Hatası: " . $e->getMessage());
            
            return response()->json([
                'error' => "Yapay zeka (AWS Bedrock) bağlantı veya kod hatası.",
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
