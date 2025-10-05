<?php

namespace App\Controller;

use App\Dto\PetDto;
use App\Form\PetType;
use App\Form\PetUploadType;
use App\Services\PetstoreClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/pets')]
class PetController extends AbstractController
{
    public function __construct(private PetstoreClient $api)
    {
    }

    #[Route('', name: 'pet_index', methods: ['GET'])]
    public function index(Request $req): Response
    {
        $status = $req->query->all('status') ?: ['available'];
        try {
            $rows = $this->api->findByStatus($status);
        } catch (\Throwable $e) {
            $this->addFlash('danger', $e->getMessage());
            $rows = [];
        }

        return $this->render('pet/index.html.twig', [
            'rows' => $rows,
            'status' => $status
        ]);
    }

    #[Route('/new', name: 'pet_new', methods: ['GET', 'POST'])]
    public function new(Request $req): Response
    {
        $dto = new PetDto();
        $form = $this->createForm(PetType::class, $dto)->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $row = $this->api->create($dto);
                $this->addFlash('success', 'Pet utworzony (ID: ' . $row['id'] . ')');
                return $this->redirectToRoute('pet_show', ['id' => $row['id']]);
            } catch (\Throwable $e) {
                $this->addFlash('danger', 'Błąd podczas tworzenia');
            }
        }

        return $this->render('pet/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nowy Pet',
            'submitLabel' => 'Dodaj'
        ]);
    }

    #[Route('/{id}', name: 'pet_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        try {
            $row = $this->api->getById($id);
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Nie znaleziono ' . $id);
            return $this->redirectToRoute('pet_index');
        }

        $dto = $this->mapRowToDto($row);

        return $this->render('pet/show.html.twig',
            ['pet' => $dto, 'raw' => $row]
        );
    }

    #[Route('/{id}/upload', name: 'pet_upload', methods: ['GET', 'POST'])]
    public function upload(int $id, Request $req): Response
    {
        $form = $this->createForm(PetUploadType::class)->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                $meta = (string)$form->get('additionalMetadata')->getData();
                $this->api->uploadImage($id, $file, $meta ?: null);
                $this->addFlash('success', 'Plik wysłany.');
                return $this->redirectToRoute('pet_show', ['id' => $id]);
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('pet/upload.html.twig', [
            'form' => $form->createView(),
            'id' => $id
        ]);
    }

    #[Route('/{id}/edit', name: 'pet_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $req): Response
    {
        try {
            $row = $this->api->getById($id);
        } catch (\Throwable $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('pet_index');
        }

        $dto = $this->mapRowToDto($row);
        $form = $this->createForm(PetType::class, $dto)->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->api->update($dto);
                $this->addFlash('success', 'Zapisano zmiany');
                return $this->redirectToRoute('pet_show', ['id' => $id]);
            } catch (\Throwable $e) {
                $this->addFlash('danger', 'Nie znaleziono ' . $id);
            }
        }

        return $this->render('pet/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edycja Pet #' . $id,
            'submitLabel' => 'Zapisz'
        ]);
    }

    #[Route('/{id}/delete', name: 'pet_delete', methods: ['POST'])]
    public function delete(int $id, Request $req): Response
    {
        if (!$this->isCsrfTokenValid('delete_pet_' . $id, $req->request->get('_token'))) {
            $this->addFlash('danger', 'Nieprawidłowy token CSRF');
            return $this->redirectToRoute('pet_show', ['id' => $id]);
        }
        try {
            $this->api->delete($id);
            $this->addFlash('success', 'Usunięto');
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Błąd podczas usuwania');
        }

        return $this->redirectToRoute('pet_index');
    }

    private function mapRowToDto(array $r): PetDto
    {
        $petDto = new PetDto();
        $petDto->id = $r['id'] ?? null;
        $petDto->name = $r['name'] ?? '';
        $petDto->status = $r['status'] ?? 'available';
        $petDto->photoUrls = $r['photoUrls'] ?? [];
        $petDto->categoryName = $r['category']['name'] ?? null;
        if (!empty($r['tags']) && is_array($r['tags'])) {
            $petDto->tagsCsv = implode(', ', array_map(fn($t) => $t['name'] ?? '', $r['tags']));
        }

        return $petDto;
    }
}
