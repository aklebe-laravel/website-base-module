<?php

namespace Modules\WebsiteBase\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;

class Changelog extends ModelBase
{
    /**
     * Should be overwritten to decide the current object is owned by user
     * canEdit() can call canManage() but don't call canEdit() in canManage()!
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return ($this->isOwnUser() || $this->canManage());
    }

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        $defaultSettings = $this->getDefaultFormSettingsByPermission();

        return [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->jsonResource, 'id'),
            'tab_controls' => [
                'base_item' => [
                    'disabled'  => $defaultSettings['disabled'],
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'                => [
                                        'html_element' => 'hidden',
                                        'label'        => 'ID',
                                        'validator'    => [
                                            'nullable',
                                            'integer'
                                        ],
                                    ],
                                    'is_public'         => [
                                        'html_element' => 'switch',
                                        'label'        => __('Public'),
                                        'description'  => __('Public to show in Frontend.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'path'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Path'),
                                        'description'  => __('Path'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'hash'              => [
                                        'html_element' => 'text',
                                        'label'        => __('Hash'),
                                        'description'  => __('Commit Hash'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'author'            => [
                                        'html_element' => 'text',
                                        'label'        => __('Author'),
                                        'description'  => __('Git Author'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255'
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'commit_created_at' => [
                                        'html_element' => 'date',
                                        'label'        => __('Commit Date'),
                                        'description'  => __('Commit was created at.'),
                                        'validator'    => ['nullable', 'date'],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'acl_resources'     => [
                                        'html_element' => 'object_to_json',
                                        'label'        => __('Values'),
                                        'description'  => __('Json list of permission resources.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'messages'          => [
                                        'html_element' => 'textarea',
                                        'options'      => ['rows' => 8],
                                        'label'        => __('Messages'),
                                        'description'  => __('The Git Messages. Can modified here.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'messages_staff'    => [
                                        'html_element' => 'textarea',
                                        'options'      => ['rows' => 8],
                                        'label'        => __('Staff Messages'),
                                        'description'  => __('Markdown changelog messages for staff.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                    'messages_public'   => [
                                        'html_element' => 'textarea',
                                        'options'      => ['rows' => 8],
                                        'label'        => __('Public Messages'),
                                        'description'  => __('Markdown changelog messages for public view.'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:30000'
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

}