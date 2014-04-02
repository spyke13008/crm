<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber;
use OroCRM\Bundle\MagentoBundle\Form\EventListener\SoapConnectorsFormSubscriber;

class SoapTransportSettingFormType extends AbstractType
{
    const NAME = 'orocrm_magento_soap_transport_setting_form_type';

    /** @var TransportInterface */
    protected $transport;

    /** @var SoapSettingsFormSubscriber */
    protected $subscriber;

    /** @var TypesRegistry */
    protected $registry;

    public function __construct(
        TransportInterface $transport,
        SoapSettingsFormSubscriber $subscriber,
        TypesRegistry $registry
    ) {
        $this->transport  = $transport;
        $this->subscriber = $subscriber;
        $this->registry   = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);

        $builder->add(
            'wsdlUrl',
            'text',
            ['label' => 'orocrm.magento.magentosoaptransport.wsdl_url.label', 'required' => true]
        );
        $builder->add(
            'apiUser',
            'text',
            ['label' => 'orocrm.magento.magentosoaptransport.api_user.label', 'required' => true]
        );
        $builder->add(
            'apiKey',
            'password',
            ['label' => 'orocrm.magento.magentosoaptransport.api_key.label', 'required' => true]
        );
        $builder->add(
            'isWsiMode',
            'checkbox',
            ['label' => 'orocrm.magento.magentosoaptransport.wsi_mode.label', 'required' => false]
        );
        $builder->add(
            'syncStartDate',
            'oro_date',
            [
                'label'      => 'orocrm.magento.magentosoaptransport.sync_start_date.label',
                'required'   => true,
                'tooltip'    => 'Provide the start date you wish to import data from.',
                'empty_data' => new \DateTime('2007-01-01', new \DateTimeZone('UTC'))
            ]
        );
        $builder->add('check', 'button', ['label' => 'Check connection']);
        $builder->add(
            'websiteId',
            'choice',
            [
                'label'    => 'orocrm.magento.magentosoaptransport.website_id.label',
                'required' => true,
                'tooltip'  => 'List could be refreshed using connection settings filled above.',
            ]
        );
        $builder->add(
            $builder->create('websites', 'hidden')
                ->addViewTransformer(new ArrayToJsonTransformer())
        );
        $builder->add(
            $builder
                ->create('isExtensionInstalled', 'hidden')
                ->addEventSubscriber(new SoapConnectorsFormSubscriber($this->registry))
        );

        $builder->add(
            'adminUrl',
            'text',
            ['label' => 'orocrm.magento.magentosoaptransport.admin_url.label', 'required' => true]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->transport->getSettingsEntityFQCN()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
