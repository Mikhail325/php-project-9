<?php

namespace Hexlet\Code;

use DiDom\Document;
use GuzzleHttp\Client;

class DataUrl
{
    public static function getData(string $url): mixed
    {
        $client = new Client();
        $respons = $client->request('GET', $url);
        $statusCode = $respons->getStatusCode();
        $body = $respons->getBody()->getContents();

        /** @var Document $document */
        $document = new Document($body);
        $h1 = $document->has('h1') ? optional($document->find('h1')[0])->text() : null;
        $title = $document->has('title') ? optional($document->find('title')[0])->text() : null;
        $description = $document->has('meta[name=description]') ?
            optional($document->find('meta[name=description]')[0])
            ->attr('content') : null;

        return [
            'description' => $description,
            'title' => $title,
            'h1' => $h1,
            'status_code' => $statusCode
        ];
    }
}
