<?php

namespace App\Http\Resources;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;

class BasicJsonResource extends JsonResource
{
    public function __construct(private ?array $data = null,
                                private ?string $message = null) {}

    public function withResponse($request, $response)
    {
        $response->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $output = [];
        if ($this->data) $output['data'] = $this->data;
        if ($this->message) $output['message'] = $this->message;

        return $output;
    }
}
