<?php
/**
 * Viacheslav Rodionov
 * viacheslav@rodionov.top
 * Date: 11.09.2022
 * Time: 0:06В
 */



use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\Unsorted\FormsUnsortedCollection;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiErrorResponseException;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Filters\Interfaces\HasOrderInterface;
use AmoCRM\Filters\UnsortedFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\AccountModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFields\MultiselectCustomFieldModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\SelectCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextareaCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\SelectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextareaCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\SelectCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextareaCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\NoteModel;
use AmoCRM\Models\NoteType\AttachmentNote;
use AmoCRM\Models\NoteType\CommonNote;
use AmoCRM\Models\NoteType\OnlyTextParamNote;
use AmoCRM\Models\NoteType\ServiceMessageNote;
use AmoCRM\Models\Unsorted\BaseUnsortedModel;
use AmoCRM\Models\Unsorted\FormsMetadata;
use AmoCRM\Models\Unsorted\FormUnsortedModel;
use AmoCRM\OAuth2\Client\Provider\AmoCRMException;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\Dotenv\Dotenv;


use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\EntitiesServices\Interfaces\HasParentEntity;
use AmoCRM\Filters\NotesFilter;
use AmoCRM\Models\Factories\NoteFactory;
use AmoCRM\Models\Interfaces\CallInterface;
use AmoCRM\Models\NoteType\CallInNote;
use AmoCRM\Models\NoteType\SmsOutNote;
use Ramsey\Uuid\Uuid;

include_once '../vendor/autoload.php';


$data = [
    "lang" => "",
    "name" => "test amo",
    "phone" => "555556666",
    "email" => "",
    "services" => "7988638",
    "comment" => " test amo",
    "page" => "/ru/service/proekt-planirovki/",
    "contacts" => "WhatsApp",
    "packet" => "B1",
    "options" => "Обмеры",
    "url" => "modal_request",
    "utm_source" => "",
    "utm_content" => "",
    "service" => "Проект планировки",
    "firstName" => "andrew",
    "lastName" => "Kryakunov",
];

$dotenv = new Dotenv;
$dotenv->load('../.env');

$apiClient = new AmoCRMApiClient(
    $_ENV['CLIENT_ID'], $_ENV['CLIENT_SECRET'], $_ENV['CLIENT_REDIRECT_URI']
);

$apiClient->setAccountBaseDomain($_ENV['ACCOUNT_DOMAIN']);

$rawToken = json_decode(file_get_contents('../token.json'), 1);
$token = new AccessToken($rawToken);

$apiClient->setAccessToken($token);

$lead = (new LeadModel)
    ->setName("Новая сделка mac book 2")
    ->setPrice(100);

$fields = new CustomFieldsValuesCollection();

$title = addTitle();
$fields->add($title);

$price = addPrice();
$fields->add($price);

$packet = addPacket($data);
$fields->add($packet);

$service = addService($data);
$fields->add($service);

$lead->setCustomFieldsValues($fields);

try {
    $lead = $apiClient->leads()->addOne($lead);
    echo '<br>lead send';
} catch (AmoCRMApiException $e) {
    throw new \Exception($e->getTitle());
}


$notesCollection = (new NotesCollection())
    ->add((new CommonNote())
            ->setEntityId($lead->getId())
            ->setText('hello2 amo!'.PHP_EOL.PHP_EOL.'hey')
            ->setCreatedBy(0)
    );

try {
    $lead = $apiClient->notes(EntityTypesInterface::LEADS)->add($notesCollection);
    echo '<br>notes send';
} catch (AmoCRMApiException $e) {
    throw new \Exception($e->getTitle());
}


//////////////////////////////
//////////////////////////////
//////////////////////////////


$unsortedContactsCollection = new ContactsCollection();
$unsortedContact = createUnsortedContact($data);
$unsortedContactsCollection->add($unsortedContact);

try {
    $contactsCollection = $apiClient->contacts()->add($unsortedContactsCollection);
    echo '<br>contacts send';
} catch (AmoCRMApiException $e) {
    throw new \Exception($e->getTitle());
}
//$lead->setContacts($contactsCollection);

$formUnsorted = new FormUnsortedModel();
$formMetadata = new FormsMetadata();
$formMetadata
    ->setFormId('site_form')
    ->setFormName('Обратная связь')
    ->setFormPage($data['page'])
    ->setFormSentAt(mktime(date('h'), date('i'), date('s')));
$unsortedService = $apiClient->unsorted();

$lead = new LeadModel();

$formUnsorted
    ->setSourceName('atischler.ru')
    ->setSourceUid('my_unique_uid')
    ->setCreatedAt(time())
    ->setMetadata($formMetadata)
    ->setLead($lead)
    ->setPipelineId(9352550)
    ->setContacts($unsortedContactsCollection);


$formsUnsortedCollection = new FormsUnsortedCollection();
$formsUnsortedCollection->add($formUnsorted);

try {
    $formsUnsortedCollection = $unsortedService->add($formsUnsortedCollection);
    echo '<br> unsorted process...';
} catch (AmoCRMApiException $e) {
    throw new \Exception($e->getTitle());
}
$formUnsorted = $formsUnsortedCollection->first();

try {
    $unsortedFiler = new UnsortedFilter();
    $unsortedFiler
        ->setCategory([BaseUnsortedModel::CATEGORY_CODE_FORMS,  BaseUnsortedModel::CATEGORY_CODE_SIP])
        ->setOrder('created_at', HasOrderInterface::SORT_ASC);
    $unsortedCollection = $unsortedService->get($unsortedFiler);

    echo '<br> ok!';
} catch (AmoCRMApiException $e) {
    throw new \Exception($e->getTitle());
}



/////////////////////////
$formsUnsortedCollection = new FormsUnsortedCollection();
$formsUnsortedCollection->add($formUnsorted);

try {
    $formsUnsortedCollection = $unsortedService->add($formsUnsortedCollection);
    echo '<br>forms send';
} catch (AmoCRMApiException $e) {
    throw new \Exception($e->getTitle());
}

$formUnsorted = $formsUnsortedCollection->first();

try {
    $unsortedFiler = new UnsortedFilter();
    $unsortedFiler
        ->setCategory([BaseUnsortedModel::CATEGORY_CODE_FORMS,  BaseUnsortedModel::CATEGORY_CODE_SIP])
        ->setOrder('created_at', HasOrderInterface::SORT_ASC);

    $unsortedCollection = $unsortedService->get($unsortedFiler);

    echo '<br> ok!';
} catch (AmoCRMApiException $e) {
    throw new \Exception($e->getTitle());
}
die;


//////////////////////////////
//////////////////////////////
//////////////////////////////


function addPacket($data)
{
    $selectCustomFieldValueModel = new SelectCustomFieldValuesModel();
    $selectCustomFieldValueModel->setFieldId(1478009);
    $selectCustomFieldValueModel->setValues(
        (new SelectCustomFieldValueCollection())
            ->add((new TextCustomFieldValueModel())->setValue($data['packet']))
    );

    return $selectCustomFieldValueModel;
}

function addService($data): SelectCustomFieldValuesModel
{
    $selectCustomFieldValueModel = new SelectCustomFieldValuesModel();
    $selectCustomFieldValueModel->setFieldId(1479049);
    $selectCustomFieldValueModel->setValues(
        (new SelectCustomFieldValueCollection())
            ->add((new SelectCustomFieldValueModel())->setValue($data['service']))
    );

    return $selectCustomFieldValueModel;
}

function addPrice(): NumericCustomFieldValuesModel
{
    $selectCustomFieldValueModel = new NumericCustomFieldValuesModel();
    $selectCustomFieldValueModel->setFieldId(1476701);
    $selectCustomFieldValueModel->setValues(
        (new NumericCustomFieldValueCollection)
            ->add((new NumericCustomFieldValueModel)->setValue(500))
    );

    return $selectCustomFieldValueModel;
}

function addTitle(): TextCustomFieldValuesModel
{
    $selectCustomFieldValueModel = new TextCustomFieldValuesModel();
    $selectCustomFieldValueModel->setFieldId(1476699)->setValues(
        (new TextCustomFieldValueCollection)->add(
            (new TextCustomFieldValueModel)->setValue('мак бук'))
    );

    return $selectCustomFieldValueModel;
}

function createUnsortedContact($data)
{
    $unsortedContact = new ContactModel();
    $unsortedContact->setName('Заявка с сайта');


    $unsortedContact->setFirstName($data['firstName']);
    $unsortedContact->setLastName($data['lastName']);

//    $phoneFieldValueModel = new MultitextCustomFieldValuesModel();
//    $phoneFieldValueModel->setFieldCode('PHONE');
//    $phoneFieldValueModel->setFieldId(976284)
//        ->setFieldName("Телефон");
//    $phoneFieldValueModel->setValues(
//        (new MultitextCustomFieldValueCollection())
//            ->add((new MultitextCustomFieldValueModel())->setValue($data['phone']))
//            ->add((new MultitextCustomFieldValueModel())->setEnumId(2216972))
//            ->add((new MultitextCustomFieldValueModel())->setEnum('WORK'))
//    );

///

    $contactCustomFields = new CustomFieldsValuesCollection();
    $phoneFieldValueModel = new MultitextCustomFieldValuesModel();
    $phoneFieldValueModel->setFieldCode('PHONE');
    $phoneFieldValueModel->setValues(
        (new MultitextCustomFieldValueCollection())
            ->add((new MultitextCustomFieldValueModel())->setValue('+79123456789'))
    );
    $unsortedContact->setCustomFieldsValues($contactCustomFields->add($phoneFieldValueModel));
   // $unsortedContactsCollection->add($unsortedContact);

    ///

//    $emailField = (new MultitextCustomFieldValuesModel())
//        ->setFieldCode('EMAIL')
//        ->setFieldId(976286)
//        ->setFieldName("Email");
//
//    $emailField->setValues(
//        (new MultiselectCustomFieldValueCollection())
//            ->add(
//                (new MultitextCustomFieldValueModel())
//                    ->setEnumId(2216984)
//                    ->setEnum('WORK')
//                    ->setValue($data['email'])
//            )
//    );

//    $contactCustomFields = new CustomFieldsValuesCollection();
//
//    $unsortedContact->setCustomFieldsValues($contactCustomFields->add($phoneFieldValueModel));
//    $unsortedContact->setCustomFieldsValues($contactCustomFields->add($emailField));

    return $unsortedContact;
}