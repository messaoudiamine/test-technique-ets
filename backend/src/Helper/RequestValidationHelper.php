<?php

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class RequestValidationHelper
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    public function deserializeRequest(Request $request, string $dtoClass): mixed
    {
        try {
            return $this->serializer->deserialize(
                $request->getContent(),
                $dtoClass,
                'json'
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['message' => 'Invalid JSON format', 'error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function validateDto(object $dto, array $groups = null): ?JsonResponse
    {
        $errors = $this->validator->validate($dto, null, $groups);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

          return new JsonResponse(
                [
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return null;
    }
}

