<?php

namespace Ziffity\Coproduct\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Rule\Model\Condition\AbstractCondition;
use Ziffity\Coproduct\Model\RuleFactory;
use Magento\Rule\Block\Conditions as BlockConditions;

class Conditions extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Fieldset $fieldset
     * @param BlockConditions $conditions
     * @param RuleFactory $ruleFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        protected Fieldset $fieldset,
        protected BlockConditions $conditions,
        protected RuleFactory $ruleFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    private function getRuleFactory()
    {
        return $this->ruleFactory->create();
    }


    /**
     * Prepare conditions form
     *
     * @return Conditions
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_product');
        $ruleFactoryModel = $this->getRuleFactory();

        $ruleFactoryModel->setConditionsSerialized($model->getConditions());

        $fieldsetId = 'conditions_fieldset';

        $formName = 'product_form';
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $conditionsFieldSetId = $ruleFactoryModel->getConditionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'coproduct/rules/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->getLayout()->createBlock(Fieldset::class);
        $renderer->setTemplate('Ziffity_Coproduct::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' =>  __('Apply the rule only if the following conditions are met (leave blank for all products).'),
                'class' => 'fieldset'
            ]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $ruleFactoryModel
        )->setRenderer(
            $this->conditions
        );

        $form->setValues($ruleFactoryModel->getData());
        $this->setConditionFormName($ruleFactoryModel->getConditions(), $formName, $conditionsFieldSetId);

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Sets form name for Condition section.
     *
     * @param AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName, $jsFormName)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
