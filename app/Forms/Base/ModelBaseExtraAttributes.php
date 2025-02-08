<?php

namespace Modules\WebsiteBase\app\Forms\Base;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Form\app\Forms\Base\ModelBase;
use Modules\SystemBase\app\Models\JsonViewResponse;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;

class ModelBaseExtraAttributes extends ModelBase
{
    /**
     * Creates a tab config for all extra attributes available
     *
     * @param  JsonResource|null  $dataSource
     *
     * @return array
     */
    public function getTabExtraAttributes(?JsonResource $dataSource = null): array
    {
        /** @var TraitAttributeAssignment $dataSource */
        $formElementsExtraAttributes = [];

        if ($dataSource) {
            foreach ($dataSource->getModelAttributeAssigmentCollection() as $extraAttribute) {
                $formElementsExtraAttributes['extra_attributes_'.$extraAttribute->modelAttribute->code] = $this->getExtraAttributeElement($extraAttribute);
            }
        }

        $tab = [
            // can be also enabled if creating new ones
            'tab'     => [
                'label' => __('Extra Attributes'),
            ],
            'content' => [
                'form_elements' => $formElementsExtraAttributes,
            ],
        ];

        return $tab;
    }

    /**
     * @param  ModelAttributeAssignment|null  $extraAttribute
     *
     * @return array
     */
    protected function getExtraAttributeElement(?ModelAttributeAssignment $extraAttribute): array
    {
        if (!$extraAttribute) {
            return [];
        }

        $description = $extraAttribute->description ?: $extraAttribute->modelAttribute->description;
        $description = __($description);

        $attributeInputData = $this->getElementModuleInfo($extraAttribute->attribute_input);
        $attributeInputModule = $attributeInputData['module'];
        $attributeInput = $attributeInputData['element'];

        return [
            'name'         => 'extra_attributes.'.$extraAttribute->modelAttribute->code,
            //'property'         => 'extra_attributes.'.$extraAttribute->modelAttribute->code,
            'html_element' => $attributeInputModule ? ($attributeInputModule.'::'.$attributeInput) : $attributeInput,
            'label'        => $description,
            'description'  => $description.' (Extra Attribute)',
            // @todo: decide dynamic
            'validator'    => ['nullable'],
            // @todo: decide dynamic
            // 'css_group'           => 'col-12 '.(($extraAttribute->attribute_input !== 'textarea') ? 'col-md-6' : ''),
            'css_group'    => $extraAttribute->form_css ? $extraAttribute->form_css : 'col-12 '.(($extraAttribute->attribute_input !== 'textarea') ? 'col-md-6' : ''),
        ];
    }

    /**
     * @param  array             $itemData
     * @param  JsonViewResponse  $jsonResponse
     * @param  mixed             $objectInstance
     *
     * @return bool
     */
    public function onBeforeUpdateItem(array $itemData, JsonViewResponse $jsonResponse, mixed $objectInstance): bool
    {
        $this->setExtraAttributesIfNeeded($objectInstance, $itemData);

        return true;
    }


    /**
     * @param  mixed  $objectInstance
     * @param  array  $itemData
     *
     * @return bool
     */
    protected function setExtraAttributesIfNeeded(mixed $objectInstance, array $itemData): bool
    {
        if (app('system_base')->hasInstanceClassOrTrait($objectInstance, TraitAttributeAssignment::class)) {
            if (isset($itemData['extra_attributes'])) {
                //$objectInstance->getExtraAttributes(); // force read extra attributes
                $objectInstance->setExtraAttributes($itemData['extra_attributes']);

                return true;
            }
        }

        return false;
    }

    /**
     * @param  array  $itemData
     *
     * @return array
     */
    public function getCleanObjectDataForSaving(array $itemData): array
    {
        $result = parent::getCleanObjectDataForSaving($itemData);

        // also remove extra_attributes
        if (app('system_base')->hasInstanceClassOrTrait($this->getObjectEloquentModelName(), TraitAttributeAssignment::class)) {
            unset($result['extra_attributes']);
            // Log::info('extra_attributes deleted!', [__METHOD__]);
        }

        return $result;
    }

}