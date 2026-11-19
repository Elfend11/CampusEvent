<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'registration_start' => ['sometimes', 'required', 'date'],
            'registration_end' => ['sometimes', 'required', 'date'],
            'event_start' => ['sometimes', 'required', 'date'],
            'event_end' => ['sometimes', 'required', 'date'],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1'],
            'ticket_price' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var \App\Models\Event $event */
                $event = $this->route('event');

                $registrationStart = $this->input('registration_start', $event->registration_start);
                $registrationEnd = $this->input('registration_end', $event->registration_end);
                $eventStart = $this->input('event_start', $event->event_start);
                $eventEnd = $this->input('event_end', $event->event_end);

                if (strtotime((string) $registrationEnd) < strtotime((string) $registrationStart)) {
                    $validator->errors()->add('registration_end', 'Waktu selesai pendaftaran harus setelah waktu mulai pendaftaran.');
                }

                if (strtotime((string) $eventStart) < strtotime((string) $registrationEnd)) {
                    $validator->errors()->add('event_start', 'Waktu mulai event harus setelah waktu selesai pendaftaran.');
                }

                if (strtotime((string) $eventEnd) <= strtotime((string) $eventStart)) {
                    $validator->errors()->add('event_end', 'Waktu selesai event harus setelah waktu mulai event.');
                }

                if ($this->has('capacity')) {
                    $minimum = $event->reserved_count + $event->sold_count;

                    if ((int) $this->input('capacity') < $minimum) {
                        $validator->errors()->add(
                            'capacity',
                            "Kapasitas tidak boleh lebih kecil dari jumlah reservasi dan tiket terjual ({$minimum}).",
                        );
                    }
                }
            },
        ];
    }
}
