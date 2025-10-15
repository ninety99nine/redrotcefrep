<?php

namespace App\Http\Requests\Analytic;

use App\Enums\AnalyticsType;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ShowAnalyticsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'type' => ['required', 'string', 'in:' . implode(',', AnalyticsType::values())],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'interval' => ['required', 'in:daily,weekly,monthly']
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->type ?? AnalyticsType::PAGE_VIEWS->value,
            'start_date' => $this->start_date ?? now()->subDays(30)->toDateString(),
            'end_date' => $this->end_date ?? now()->toDateString(),
            'interval' => $this->interval ?? 'daily'
        ]);
    }

    /**
     * Get the validated analytics type.
     *
     * @return AnalyticsType
     */
    public function analyticsType(): AnalyticsType
    {
        return AnalyticsType::from($this->validated()['type']);
    }

    /**
     * Get the validated start date as a Carbon instance.
     *
     * @return Carbon
     */
    public function startDate(): Carbon
    {
        return Carbon::parse($this->validated()['start_date']);
    }

    /**
     * Get the validated end date as a Carbon instance.
     *
     * @return Carbon
     */
    public function endDate(): Carbon
    {
        return Carbon::parse($this->validated()['end_date']);
    }

    /**
     * Get the validated interval.
     *
     * @return string
     */
    public function interval(): string
    {
        return $this->validated()['interval'];
    }
}
