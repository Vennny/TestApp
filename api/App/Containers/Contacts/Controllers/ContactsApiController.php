<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Controllers;

use App\Containers\Contacts\Actions\CreateContactAction;
use App\Containers\Contacts\Actions\DeleteContactAction;
use App\Containers\Contacts\Actions\GetAllContactsAction;
use App\Containers\Contacts\Actions\GetContactAction;
use App\Containers\Contacts\Actions\UpdateContactAction;
use App\Containers\Contacts\Requests\ContactRequestFilter;
use App\Containers\Contacts\Transformers\ContactApiTransformer;
use App\Ship\Parents\Controllers\ApiController;
use App\Ship\Responses\ApiResponse;
use Illuminate\Http\Request;

final class ContactsApiController extends ApiController
{
    /**
     * GET: Get collection of Contacts.
     *
     * @param \App\Containers\Contacts\Actions\GetAllContactsAction $getAllAction
     *
     * @return \App\Ship\Responses\ApiResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function index(GetAllContactsAction $getAllAction): ApiResponse
    {
        return $this->queryResponse($getAllAction->query(), ContactApiTransformer::class);
    }

    /**
     * GET: Get single Contact.
     *
     * @param \App\Containers\Contacts\Actions\GetContactAction $getAction
     * @param int|string $contactId
     *
     * @return \App\Ship\Responses\ApiResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function show(GetContactAction $getAction, int | string $contactId): ApiResponse
    {
        $contact = $getAction->run((int) $contactId);

        return $this->modelResponse($contact, ContactApiTransformer::class);
    }

    /**
     * POST: Store new Contact.
     *
     * @param \App\Containers\Contacts\Requests\ContactRequestFilter $requestFilter
     * @param \App\Containers\Contacts\Actions\CreateContactAction $createAction
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Ship\Responses\ApiResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function store(
        ContactRequestFilter $requestFilter,
        CreateContactAction $createAction,
        Request $request
    ): ApiResponse {
        $data = $requestFilter->getValidatedData($request);
        $contact = $createAction->run($data);

        return $this->modelResponse($contact, ContactApiTransformer::class)->setStatusCode(201);
    }

    /**
     * PUT/PATCH: Update Contact.
     *
     * @param \App\Containers\Contacts\Requests\ContactRequestFilter $requestFilter
     * @param \App\Containers\Contacts\Actions\GetContactAction $getAction
     * @param \App\Containers\Contacts\Actions\UpdateContactAction $updateAction
     * @param \Illuminate\Http\Request $request
     * @param int|string $contactId
     *
     * @return \App\Ship\Responses\ApiResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function update(
        ContactRequestFilter $requestFilter,
        GetContactAction $getAction,
        UpdateContactAction $updateAction,
        Request $request,
        int | string $contactId
    ): ApiResponse {
        $contact = $getAction->run((int) $contactId);

        $data = $requestFilter->getValidatedData($request, $contact);
        $contact = $updateAction->run($contact, $data);

        return $this->modelResponse($contact, ContactApiTransformer::class);
    }

    /**
     * DELETE: Delete Contact.
     *
     * @param \App\Containers\Contacts\Actions\GetContactAction $getAction
     * @param \App\Containers\Contacts\Actions\DeleteContactAction $deleteAction
     * @param int|string $contactId
     *
     * @return \App\Ship\Responses\ApiResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function destroy(
        GetContactAction $getAction,
        DeleteContactAction $deleteAction,
        int | string $contactId
    ): ApiResponse {
        $contact = $getAction->run((int) $contactId);

        $deleteAction->run($contact);

        return $this->emptyResponse();
    }
}
