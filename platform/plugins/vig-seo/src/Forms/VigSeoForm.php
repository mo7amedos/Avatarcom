<?php

namespace VigStudio\VigSeo\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use VigStudio\VigSeo\Http\Requests\VigSeoRequest;
use VigStudio\VigSeo\Models\VigSeo;

class VigSeoForm extends FormAbstract
{
    public function buildForm()
    {
        $this
            ->setupModel(new VigSeo)
            ->setValidatorClass(VigSeoRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
