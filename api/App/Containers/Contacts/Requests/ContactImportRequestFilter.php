<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Requests;

use App\Ship\Values\Enums\MimeTypesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\ValidationException;

final class ContactImportRequestFilter
{
    /**
     * Fields.
     */
    final public const string FIELD_FILE = 'file';

    /**
     * @param \Illuminate\Validation\Factory $validatorFactory
     */
    public function __construct(
        private readonly ValidatorFactory $validatorFactory,
    ) {
    }

    /**
     * Get values for model.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\UploadedFile
     */
    public function getValidatedFile(Request $request): UploadedFile {
        $this->validate($request);
        return $request->file(self::FIELD_FILE);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return string[]
     */
    public function validate(Request $request): array
    {
        $rules = $this->getRules($request);
        $validator = $this->validatorFactory->make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return \array_keys($rules);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed[]
     */
    private function getRules(Request $request): array
    {
        return [
            self::FIELD_FILE => [
                'required',
                'file',
                'mimetypes:' . MimeTypesEnum::TEXT_XML->value . ',' . MimeTypesEnum::APPLICATION_XML->value,
            ],
        ];
    }
}
