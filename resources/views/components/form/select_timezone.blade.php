@php
    use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
    use Modules\WebsiteBase\app\Services\Setting;

    /**
     * Input of the user setting 'timezone' (extra attribute, deployment 0018): empty = time zone of the installation.
     *
     * @var NativeObjectBase $form_instance
     * @var array $data
     */

    $data['options'] = ['' => __('Default of the installation (:timezone)', ['timezone' => app('website_base_settings')->installationTimezone()])]
        + Setting::timezoneOptions();
@endphp
<div>
    @include('form::components.form.select')
</div>
