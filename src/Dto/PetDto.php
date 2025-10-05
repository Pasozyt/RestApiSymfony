<?php

namespace App\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class PetDto
{
    public ?int $id = null;

    #[Assert\NotBlank]
    public string $name = '';

    #[Assert\Choice(choices: ['available','pending','sold'])]
    public string $status = 'available';

    /** @var string[] */
    public array $photoUrls = [];

    public ?string $categoryName = null;
    public ?string $tagsCsv = null;
}
