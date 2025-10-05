<?php

namespace App\Services;

use App\Dto\PetDto;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class PetstoreClient
{
    public function __construct(
        private HttpClientInterface $http,
        private string $baseUrl,
        private string $apiKey
    ) {}

    /** @return array<int, array> */
    public function findByStatus(array $statuses = ['available']): array
    {
        $query  = implode('&', array_map(fn($s) => 'status=' . rawurlencode($s), $statuses));
        $url   = rtrim($this->baseUrl, '/').'/pet/findByStatus?'.$query;
        $res   = $this->http->request('GET', $url, ['headers'=>['Accept'=>'application/json']]);
        $this->ensureOk($res->getStatusCode(), $res->getContent(false));

        return $res->toArray(false);
    }

    public function getById(int $id): array
    {
        $res = $this->http->request('GET', rtrim($this->baseUrl, '/')."/pet/$id", ['headers'=>['Accept'=>'application/json']]);
        $this->ensureOk($res->getStatusCode(), $res->getContent(false));

        return $res->toArray(false);
    }

    public function create(PetDto $dto): array
    {
        $payload = $this->toApi($dto, withId: true);
        $res = $this->http->request('POST', rtrim($this->baseUrl, '/')."/pet", [
            'headers' => ['Content-Type'=>'application/json', 'Accept'=>'application/json'],
            'json'    => $payload
        ]);
        $this->ensureCreatedOrOk($res->getStatusCode(), $res->getContent(false));

        return $res->toArray(false);
    }

    public function update(PetDto $dto): array
    {
        $payload = $this->toApi($dto, withId: true);
        $res = $this->http->request('PUT', rtrim($this->baseUrl, '/')."/pet", [
            'headers' => ['Content-Type'=>'application/json', 'Accept'=>'application/json'],
            'json'    => $payload
        ]);
        $this->ensureOk($res->getStatusCode(), $res->getContent(false));

        return $res->toArray(false);
    }

    public function delete(int $id): void
    {
        $res = $this->http->request('DELETE', rtrim($this->baseUrl, '/')."/pet/$id", [
            'content-Type'=>'application/json',
            'accept'=>'application/json',
            'api_key' => ''
        ]);
        $this->ensureOk($res->getStatusCode(), $res->getContent(false));
    }

    /** mapowanie DTO struktura Petstore */
    private function toApi(PetDto $d, bool $withId): array
    {
        $tags = [];
        if ($d->tagsCsv) {
            foreach (array_filter(array_map('trim', explode(',', $d->tagsCsv))) as $i => $t) {
                $tags[] = ['id'=>$i+1, 'name'=>$t];
            }
        }
        $payload = [
            'name'      => $d->name,
            'status'    => $d->status,
            'photoUrls' => $d->photoUrls ?: [],
            'category'  => $d->categoryName ? ['id'=>1,'name'=>$d->categoryName] : null,
            'tags'      => $tags ?: null,
        ];
        if ($withId && $d->id) $payload['id'] = $d->id;
        return array_filter($payload, fn($v) => $v !== null);
    }

    private function ensureOk(int $status, string $body): void
    {
        if ($status < 200 || $status >= 300) $this->throwHttpError($status, $body);
    }
    private function ensureCreatedOrOk(int $status, string $body): void
    {
        if (!in_array($status, [Response::HTTP_OK, Response::HTTP_CREATED], true)) $this->throwHttpError($status, $body);
    }
    private function throwHttpError(int $status, string $body): never
    {
        $msg = "Petstore error ($status): ".$body;
        throw new \RuntimeException($msg, $status);
    }
}
