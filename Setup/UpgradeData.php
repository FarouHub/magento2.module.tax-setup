<?php

namespace Lightweight\TaxSetup\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Api\TaxClassManagementInterface;

class UpgradeData implements UpgradeDataInterface
{

    protected $_taxRateFactory;
    protected $_taxRuleModel;
    protected $_taxClassManagement;
    protected $_taxClassRepository;
    protected $_searchCriteriaBuilder;
    protected $_filterBuilder;
    protected $_taxClassCollectionFactory;
    protected $_appState;
    protected $_defaultCustomerTaxClass;
    protected $_defaultProductTaxClass;
    protected $_taxClassFactory;
    protected $_customerGroupFactory;

    protected $_noVatIfVatIdKnown = [
        'Österreich' => 'AT',
        'Italien' => 'IT',
        'Spanien' => 'ES',
        'Frankreich' => 'FR',
        'Vereinigtes Königreich' => 'GB',
        'Irland' => 'IE',
        'Belgien' => 'BE',
        'Bulgarien' => 'BG',
        'Dänemark' => 'DK',
        'Estland' => 'EE',
        'Finnland' => 'FI',
        'Griechenland' => 'GR',
        'Kroatien' => 'HR',
        'Lettland' => 'LV',
        'Litauen' => 'LT',
        'Luxemburg' => 'LU',
        'Malta' => 'MT',
        'Niederlande' => 'NL',
        'Polen' => 'PL',
        'Portugal' => 'PT',
        'Rumänien' => 'RO',
        'Schweden' => 'SE',
        'Zypern' => 'CY',
        'Tschechische Republik' => 'CZ',
        'Ungarn' => 'HU',
        'Slowenien' => 'SI',
        'Slowakei' => 'SK',
    ];

    /**
     * InstallData constructor.
     *
     * @param RateFactory                 $rate
     * @param Rule                        $rule
     * @param TaxClassManagementInterface $taxClassManagement
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param FilterBuilder               $filterBuilder
     * @param CollectionFactory           $collectionFactory
     * @param State                       $appState
     */
    public function __construct(
        \Magento\Tax\Model\Calculation\RateFactory $rate,
        \Magento\Tax\Model\Calculation\Rule\Proxy $rule,
        \Magento\Tax\Api\TaxClassManagementInterface $taxClassManagement,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $collectionFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Tax\Model\ClassModelFactory $class,
        \Magento\Customer\Model\Group $group
    )
    {
        $this->_taxRateFactory = $rate;
        $this->_taxRuleModel = $rule;
        $this->_taxClassManagement = $taxClassManagement;
        $this->_taxClassRepository = $taxClassRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_taxClassCollectionFactory = $collectionFactory;
        $this->_appState = $appState;
        $this->_taxClassFactory = $class;
        $this->_customerGroupFactory = $group;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addVatFreeTaxRates($setup);
        }
    }

    public function addVatFreeTaxRates(ModuleDataSetupInterface $setup)
    {
        try {
            $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch(\Magento\Framework\Exception\LocalizedException $e) {

        }

        $setup->startSetup();

        $this->_defaultProductTaxClass = null;

        $filter = $this->_filterBuilder
            ->setField(ClassModel::KEY_TYPE)
            ->setValue(TaxClassManagementInterface::TYPE_PRODUCT)
            ->create();
        $searchCriteria = $this->_searchCriteriaBuilder->addFilters([$filter])->create();
        $searchResults = $this->_taxClassRepository->getList($searchCriteria);
        foreach($searchResults->getItems() as $taxClass) {
            if(is_null($this->_defaultProductTaxClass) || $taxClass->getClassId() < $this->_defaultProductTaxClass) {
                $this->_defaultProductTaxClass = $taxClass->getClassId();
            }
        }

        $taxClass = $this->_taxClassFactory->create();
        $taxClass->setClassName('VAT known')
            ->setClassType(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->save();
        $taxClassId = $taxClass->getId();


        $rateIds = [];

        foreach($this->_noVatIfVatIdKnown as $name => $code) {
            $rate = $this->_taxRateFactory->create();
            $rate->setCode($name . ' - NO VAT')
                 ->setTaxCountryId($code)
                 ->setTaxRegionId(0)
                 ->setZipIsRange(null)
                 ->setTaxPostCode("*")
                 ->setRate(0)
                 ->save();
            $rateIds[] = $rate->getId();
        }

        $this->_taxRuleModel
            ->setCode("Steuerfrei")
            ->setPriority(2)
            ->setCustomerTaxClassIds(array($taxClassId))
            ->setProductTaxClassIds(array($this->_defaultProductTaxClass))
            ->setTaxRateIds($rateIds)
            ->save();

        $wholesaleGroup = $this->_customerGroupFactory->load('Wholesale', 'customer_group_code');

        if($wholesaleGroup->getId()) {
            if($taxClassId) {
                $wholesaleGroup->setTaxClassId($taxClassId)
                               ->setCustomerGroupCode('Steuerfrei wenn VAT bekannt')
                               ->save();
            }
        }

        $setup->endSetup();
    }

}
