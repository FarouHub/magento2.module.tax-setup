<?php

namespace Lightweight\TaxSetup\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Api\TaxClassManagementInterface;

class InstallData implements InstallDataInterface
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

    protected $_vatCountries = [
        'Österreich' => 'AT',
        'Belgien' => 'BE',
        'Bulgarien' => 'BG',
        'Zypern' => 'CY',
        'Tschechische Republik' => 'CZ',
        'Deutschland' => 'DE',
        'Dänemark' => 'DK',
        'Estland' => 'EE',
        'Spanien' => 'ES',
        'Finnland' => 'FI',
        'Frankreich' => 'FR',
        'Vereinigtes Königreich' => 'GB',
        'Griechenland' => 'GR',
        'Kroatien' => 'HR',
        'Ungarn' => 'HU',
        'Irland' => 'IE',
        'Italien' => 'IT',
        'Litauen' => 'LT',
        'Luxemburg' => 'LU',
        'Lettland' => 'LV',
        'Malta' => 'MT',
        'Niederlande' => 'NL',
        'Polen' => 'PL',
        'Portugal' => 'PT',
        'Rumänien' => 'RO',
        'Schweden' => 'SE',
        'Slowenien' => 'SI',
        'Slowakei' => 'SK',
    ];

    protected $_nonVatCountries = [
        'Andorra' => 'AD',
        'Vereinigte Arabische Emirate' => 'AE',
        'Afghanistan' => 'AF',
        'Antigua und Barbuda' => 'AG',
        'Anguilla' => 'AI',
        'Albanien' => 'AL',
        'Armenien' => 'AM',
        'Angola' => 'AO',
        'Antarktis' => 'AQ',
        'Argentinien' => 'AR',
        'Amerikanisch Samoa' => 'AS',
        'Australien' => 'AU',
        'Aruba' => 'AW',
        'Åland Inseln' => 'AX',
        'Aserbaidschan' => 'AZ',
        'Bosnien und Herzegowina' => 'BA',
        'Barbados' => 'BB',
        'Bangladesch' => 'BD',
        'Burkina Faso' => 'BF',
        'Bahrain' => 'BH',
        'Burundi' => 'BI',
        'Benin' => 'BJ',
        'Saint-Barthélemy' => 'BL',
        'Bermuda' => 'BM',
        'Brunei Darussalam' => 'BN',
        'Bolivien' => 'BO',
        'Brasilien' => 'BR',
        'Bahamas' => 'BS',
        'Bhutan' => 'BT',
        'Bouvetinsel' => 'BV',
        'Botsuana' => 'BW',
        'Weißrussland' => 'BY',
        'Belize' => 'BZ',
        'Kanada' => 'CA',
        'Kokosinseln (Keelinginseln)' => 'CC',
        'Kongo, Dem. Rep.' => 'CD',
        'Zentralafrikanische Republik' => 'CF',
        'Kongo' => 'CG',
        'Schweiz' => 'CH',
        'Cote d´Ivoire' => 'CI',
        'Cookinseln' => 'CK',
        'Chile' => 'CL',
        'Kamerun' => 'CM',
        'China' => 'CN',
        'Kolumbien' => 'CO',
        'Costa Rica' => 'CR',
        'Kuba' => 'CU',
        'Kap Verde' => 'CV',
        'Weihnachtsinsel' => 'CX',
        'Dschibuti' => 'DJ',
        'Dominica' => 'DM',
        'Dominikanische Republik' => 'DO',
        'Algerien' => 'DZ',
        'Ecuador' => 'EC',
        'Ägypten' => 'EG',
        'Westsahara' => 'EH',
        'Eritrea' => 'ER',
        'Äthiopien' => 'ET',
        'Fidschi' => 'FJ',
        'Falklandinseln (Malwinen)' => 'FK',
        'Mikronesien, Föderierte Staaten von' => 'FM',
        'Färöer' => 'FO',
        'Gabun' => 'GA',
        'Grenada' => 'GD',
        'Georgien' => 'GE',
        'Französisch Guiana' => 'GF',
        'Guernsey' => 'GG',
        'Ghana' => 'GH',
        'Gibraltar' => 'GI',
        'Grönland' => 'GL',
        'Gambia' => 'GM',
        'Guinea' => 'GN',
        'Guadeloupe' => 'GP',
        'Äquatorialguinea' => 'GQ',
        'Südgeorgien und die Südlichen Sandwichinseln' => 'GS',
        'Guatemala' => 'GT',
        'Guam' => 'GU',
        'Guinea-Bissau' => 'GW',
        'Guyana' => 'GY',
        'Hong Kong' => 'HK',
        'Heard Insel und McDonald Inseln' => 'HM',
        'Honduras' => 'HN',
        'Haiti' => 'HT',
        'Indonesien' => 'ID',
        'Israel' => 'IL',
        'Isle of Man' => 'IM',
        'Indien' => 'IN',
        'Britisches Territorium im Indischen Ozean' => 'IO',
        'Irak' => 'IQ',
        'Iran' => 'IR',
        'Island' => 'IS',
        'Jersey' => 'JE',
        'Jamaika' => 'JM',
        'Jordanien' => 'JO',
        'Japan' => 'JP',
        'Kenia' => 'KE',
        'Kirgisistan' => 'KG',
        'Kambodscha' => 'KH',
        'Kiribati' => 'KI',
        'Komoren' => 'KM',
        'St. Kitts und Nevis' => 'KN',
        'Nordkorea' => 'KP',
        'Südkorea' => 'KR',
        'Kuwait' => 'KW',
        'Kaimaninseln' => 'KY',
        'Kasachstan' => 'KZ',
        'Laos' => 'LA',
        'Libanon' => 'LB',
        'St. Lucia' => 'LC',
        'Liechtenstein' => 'LI',
        'Sri Lanka' => 'LK',
        'Liberia' => 'LR',
        'Lesotho' => 'LS',
        'Libyen' => 'LY',
        'Marokko' => 'MA',
        'Monaco' => 'MC',
        'Moldau' => 'MD',
        'Montenegro' => 'ME',
        'Saint-Martin' => 'MF',
        'Madagaskar' => 'MG',
        'Marshallinseln' => 'MH',
        'Mazedonien' => 'MK',
        'Mali' => 'ML',
        'Myanmar' => 'MM',
        'Mongolei' => 'MN',
        'Macao' => 'MO',
        'Nördliche Marianen' => 'MP',
        'Martinique' => 'MQ',
        'Mauretanien' => 'MR',
        'Montserrat' => 'MS',
        'Mauritius' => 'MU',
        'Malediven' => 'MV',
        'Malawi' => 'MW',
        'Mexiko' => 'MX',
        'Malaysia' => 'MY',
        'Mosambik' => 'MZ',
        'Namibia' => 'NA',
        'Neukaledonien' => 'NC',
        'Niger' => 'NE',
        'Norfolkinsel' => 'NF',
        'Nigeria' => 'NG',
        'Nicaragua' => 'NI',
        'Norwegen' => 'NO',
        'Nepal' => 'NP',
        'Nauru' => 'NR',
        'Niue' => 'NU',
        'Neuseeland' => 'NZ',
        'Oman' => 'OM',
        'Panama' => 'PA',
        'Peru' => 'PE',
        'Französisch-Polynesien' => 'PF',
        'Papua-Neuguinea' => 'PG',
        'Philippinen' => 'PH',
        'Pakistan' => 'PK',
        'Saint Pierre und Miquelon' => 'PM',
        'Pitcairn' => 'PN',
        'Palästinische Gebiete' => 'PS',
        'Palau' => 'PW',
        'Paraguay' => 'PY',
        'Katar' => 'QA',
        'Réunion' => 'RE',
        'Serbien' => 'RS',
        'Russische Föderation' => 'RU',
        'Ruanda' => 'RW',
        'Saudi-Arabien' => 'SA',
        'Salomonen' => 'SB',
        'Seychellen' => 'SC',
        'Sudan' => 'SD',
        'Singapur' => 'SG',
        'Saint Helena' => 'SH',
        'Svalbard und Jan Mayen' => 'SJ',
        'Sierra Leone' => 'SL',
        'San Marino' => 'SM',
        'Senegal' => 'SN',
        'Somalia' => 'SO',
        'Suriname' => 'SR',
        'Sao Tome und Principe' => 'ST',
        'El Salvador' => 'SV',
        'Syrien' => 'SY',
        'Swasiland' => 'SZ',
        'Turks- und Caicosinseln' => 'TC',
        'Tschad' => 'TD',
        'Französische Südgebiete' => 'TF',
        'Togo' => 'TG',
        'Thailand' => 'TH',
        'Tadschikistan' => 'TJ',
        'Tokelau' => 'TK',
        'Timor-Leste' => 'TL',
        'Turkmenistan' => 'TM',
        'Tunesien' => 'TN',
        'Tonga' => 'TO',
        'Türkei' => 'TR',
        'Trinidad und Tobago' => 'TT',
        'Tuvalu' => 'TV',
        'Republik China (Taiwan)' => 'TW',
        'Tansania' => 'TZ',
        'Ukraine' => 'UA',
        'Uganda' => 'UG',
        'United States Minor Outlying Islands' => 'UM',
        'Vereinigte Staaten von Amerika' => 'US',
        'Uruguay' => 'UY',
        'Usbekistan' => 'UZ',
        'Heiliger Stuhl (Vatikanstadt)' => 'VA',
        'St. Vincent und die Grenadinen' => 'VC',
        'Venezuela' => 'VE',
        'Britische Jungferninseln' => 'VG',
        'Amerikanische Jungferninseln' => 'VI',
        'Vietnam' => 'VN',
        'Vanuatu' => 'VU',
        'Wallis und Futuna' => 'WF',
        'Samoa' => 'WS',
        'Jemen' => 'YE',
        'Mayotte' => 'YT',
        'Südafrika' => 'ZA',
        'Sambia' => 'ZM',
        'Simbabwe' => 'ZW',
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
        \Magento\Framework\App\State $appState
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

    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->createTaxRates($setup);
    }

    public function createTaxRates(ModuleDataSetupInterface $setup)
    {
        try {
            $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch(\Magento\Framework\Exception\LocalizedException $e) {

        }

        $setup->startSetup();

        $this->_defaultProductTaxClass = null;
        $this->_defaultCustomerTaxClass = null;

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

        $filter = $this->_filterBuilder
            ->setField(ClassModel::KEY_TYPE)
            ->setValue(TaxClassManagementInterface::TYPE_CUSTOMER)
            ->create();
        $searchCriteria = $this->_searchCriteriaBuilder->addFilters([$filter])->create();
        $searchResults = $this->_taxClassRepository->getList($searchCriteria);
        foreach($searchResults->getItems() as $taxClass) {
            if(is_null($this->_defaultCustomerTaxClass) || $taxClass->getClassId() < $this->_defaultCustomerTaxClass) {
                $this->_defaultCustomerTaxClass = $taxClass->getClassId();
            }
        }

        $rateIds = [];

        foreach($this->_vatCountries as $name => $code) {
            $rate = $this->_taxRateFactory->create();
            $rate->setCode($name . ' - VAT')
                ->setTaxCountryId($code)
                ->setTaxRegionId(0)
                ->setZipIsRange(null)
                ->setTaxPostCode("*")
                ->setRate(19)
                ->save();
            $rateIds[] = $rate->getId();
        }

        foreach($this->_nonVatCountries as $name => $code) {
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
            ->setCode("Regulärer Steuersatz")
            ->setPriority(0)
            ->setCustomerTaxClassIds(array($this->_defaultCustomerTaxClass))
            ->setProductTaxClassIds(array($this->_defaultProductTaxClass))
            ->setTaxRateIds($rateIds)
            ->save();

        $setup->endSetup();

    }

}
