@props(['name', 'label', 'type' => 'text', 'id' => null, 'value' => null])
@php
    $fieldId = $id ?? 'field-'.str_replace('_', '-', $name);
    $invalid = $errors->has($name);
    $description = trim(($attributes->get('aria-describedby') ?? '').($invalid ? ' '.$fieldId.'-error' : ''));
@endphp
<div class="auth-field">
    <div class="floating-control {{ $type === 'password' ? 'has-password-toggle' : '' }}" @if($type === 'password') data-password-field @endif>
        <input {{ $attributes->except('aria-describedby') }} id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" placeholder=" " @if($type !== 'password') value="{{ $value ?? old($name) }}" @endif @if($invalid) aria-invalid="true" @endif @if($description) aria-describedby="{{ $description }}" @endif>
        <label for="{{ $fieldId }}">{{ $label }}</label>
        @if($type === 'password')
        <button type="button" class="password-toggle" data-password-toggle data-password-label="{{ $label }}" aria-label="Show {{ mb_strtolower($label) }}" aria-controls="{{ $fieldId }}" aria-pressed="false" hidden>
            <svg class="password-eye" width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
            <svg class="password-eye-off" width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 3 18 18M10.6 5.1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.2 4.1M6.1 6.1C3.4 8.2 2 12 2 12s3.5 7 10 7c1.9 0 3.6-.6 5-1.4M10 10a3 3 0 0 0 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        @endif
    </div>
    @error($name)<p class="field-error" id="{{ $fieldId }}-error">{{ $message }}</p>@enderror
</div>
