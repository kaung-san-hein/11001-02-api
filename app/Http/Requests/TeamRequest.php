<?php

namespace App\Http\Requests;

class TeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => $this->id ? 'required' : 'required|unique:teams',
            'player_count' => 'required',
            'region' => 'required',
            'country' => 'required',
        ];
    }
}
