<?php

use Symfony\Component\Translation\MessageCatalogue;

$catalogue = new MessageCatalogue('sr_Cyrl', array (
  'validators' => 
  array (
    'This value should be false.' => 'Вредност треба да буде нетачна.',
    'This value should be true.' => 'Вредност треба да буде тачна.',
    'This value should be of type {{ type }}.' => 'Вредност треба да буде типа {{ type }}.',
    'This value should be blank.' => 'Вредност треба да буде празна.',
    'The value you selected is not a valid choice.' => 'Вредност треба да буде једна од понуђених.',
    'You must select at least {{ limit }} choice.|You must select at least {{ limit }} choices.' => 'Изаберите бар {{ limit }} могућност.|Изаберите бар {{ limit }} могућности.|Изаберите бар {{ limit }} могућности.',
    'You must select at most {{ limit }} choice.|You must select at most {{ limit }} choices.' => 'Изаберите највише {{ limit }} могућност.|Изаберите највише {{ limit }} могућности.|Изаберите највише {{ limit }} могућности.',
    'One or more of the given values is invalid.' => 'Једна или више вредности је невалидна.',
    'This field was not expected.' => 'Ово поље не очекује.',
    'This field is missing.' => 'Ово поље недостаје.',
    'This value is not a valid date.' => 'Вредност није валидан датум.',
    'This value is not a valid datetime.' => 'Вредност није валидан датум-време.',
    'This value is not a valid email address.' => 'Вредност није валидна адреса електронске поште.',
    'The file could not be found.' => 'Датотека не може бити пронађена.',
    'The file is not readable.' => 'Датотека није читљива.',
    'The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.' => 'Датотека је превелика ({{ size }} {{ suffix }}). Највећа дозвољена величина је {{ limit }} {{ suffix }}.',
    'The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.' => 'Миме тип датотеке није валидан ({{ type }}). Дозвољени миме типови су {{ types }}.',
    'This value should be {{ limit }} or less.' => 'Вредност треба да буде {{ limit }} или мање.',
    'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.' => 'Вредност је предугачка. Треба да има {{ limit }} карактер или мање.|Вредност је предугачка. Треба да има {{ limit }} карактера или мање.|Вредност је предугачка. Треба да има {{ limit }} карактера или мање.',
    'This value should be {{ limit }} or more.' => 'Вредност треба да буде {{ limit }} или више.',
    'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.' => 'Вредност је прекратка. Треба да има {{ limit }} карактер или више.|Вредност је прекратка. Треба да има {{ limit }} карактера или више.|Вредност је прекратка. Треба да има {{ limit }} карактера или више.',
    'This value should not be blank.' => 'Вредност не треба да буде празна.',
    'This value should not be null.' => 'Вредност не треба да буде null.',
    'This value should be null.' => 'Вредност треба да буде null.',
    'This value is not valid.' => 'Вредност је невалидна.',
    'This value is not a valid time.' => 'Вредност није валидно време.',
    'This value is not a valid URL.' => 'Вредност није валидан URL.',
    'The two values should be equal.' => 'Обе вредности треба да буду једнаке.',
    'The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.' => 'Датотека је превелика. Највећа дозвољена величина је {{ limit }} {{ suffix }}.',
    'The file is too large.' => 'Датотека је превелика.',
    'The file could not be uploaded.' => 'Датотека не може бити отпремљена.',
    'This value should be a valid number.' => 'Вредност треба да буде валидан број.',
    'This file is not a valid image.' => 'Ова датотека није валидна слика.',
    'This is not a valid IP address.' => 'Ово није валидна ИП адреса.',
    'This value is not a valid language.' => 'Вредност није валидан језик.',
    'This value is not a valid locale.' => 'Вредност није валидан локал.',
    'This value is not a valid country.' => 'Вредност није валидна земља.',
    'This value is already used.' => 'Вредност је већ искоришћена.',
    'The size of the image could not be detected.' => 'Величина слике не може бити одређена.',
    'The image width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px.' => 'Ширина слике је превелика ({{ width }}px). Најећа дозвољена ширина је {{ max_width }}px.',
    'The image width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px.' => 'Ширина слике је премала ({{ width }}px). Најмања дозвољена ширина је {{ min_width }}px.',
    'The image height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px.' => 'Висина слике је превелика ({{ height }}px). Најећа дозвољена висина је {{ max_height }}px.',
    'The image height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px.' => 'Висина слике је премала ({{ height }}px). Најмања дозвољена висина је {{ min_height }}px.',
    'This value should be the user\'s current password.' => 'Вредност треба да буде тренутна корисничка лозинка.',
    'This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.' => 'Вредност треба да има тачно {{ limit }} карактер.|Вредност треба да има тачно {{ limit }} карактера.|Вредност треба да има тачно {{ limit }} карактера.',
    'The file was only partially uploaded.' => 'Датотека је само парцијално отпремљена.',
    'No file was uploaded.' => 'Датотека није отпремљена.',
    'No temporary folder was configured in php.ini.' => 'Привремени директоријум није конфигурисан у php.ini.',
    'Cannot write temporary file to disk.' => 'Немогуће писање привремене датотеке на диск.',
    'A PHP extension caused the upload to fail.' => 'PHP екстензија је проузроковала неуспех отпремања датотеке.',
    'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.' => 'Ова колекција треба да садржи {{ limit }} или више елемената.|Ова колекција треба да садржи {{ limit }} или више елемената.|Ова колекција треба да садржи {{ limit }} или више елемената.',
    'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.' => 'Ова колекција треба да садржи {{ limit }} или мање елемената.|Ова колекција треба да садржи {{ limit }} или мање елемената.|Ова колекција треба да садржи {{ limit }} или мање елемената.',
    'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.' => 'Ова колекција треба да садржи тачно {{ limit }} елемент.|Ова колекција треба да садржи тачно {{ limit }} елемента.|Ова колекција треба да садржи тачно {{ limit }} елемената.',
    'Invalid card number.' => 'Невалидан број картице.',
    'Unsupported card type or invalid card number.' => 'Невалидан број картице или тип картице није подржан.',
    'This is not a valid International Bank Account Number (IBAN).' => 'Ово није валидан међународни број банковног рачуна (IBAN).',
    'This value is not a valid ISBN-10.' => 'Ово није валидан ISBN-10.',
    'This value is not a valid ISBN-13.' => 'Ово није валидан ISBN-13.',
    'This value is neither a valid ISBN-10 nor a valid ISBN-13.' => 'Ово није валидан ISBN-10 или ISBN-13.',
    'This value is not a valid ISSN.' => 'Ово није валидан ISSN.',
    'This value is not a valid currency.' => 'Ово није валидна валута.',
    'This value should be equal to {{ compared_value }}.' => 'Ова вредност треба да буде {{ compared_value }}.',
    'This value should be greater than {{ compared_value }}.' => 'Ова вредност треба да буде већа од {{ compared_value }}.',
    'This value should be greater than or equal to {{ compared_value }}.' => 'Ова вредност треба да буде већа или једнака {{ compared_value }}.',
    'This value should be identical to {{ compared_value_type }} {{ compared_value }}.' => 'Ова вредност треба да буде идентична са {{ compared_value_type }} {{ compared_value }}.',
    'This value should be less than {{ compared_value }}.' => 'Ова вредност треба да буде мања од {{ compared_value }}.',
    'This value should be less than or equal to {{ compared_value }}.' => 'Ова вредност треба да буде мања или једнака {{ compared_value }}.',
    'This value should not be equal to {{ compared_value }}.' => 'Ова вредност не треба да буде једнака {{ compared_value }}.',
    'This value should not be identical to {{ compared_value_type }} {{ compared_value }}.' => 'Ова вредност не треба да буде идентична са {{ compared_value_type }} {{ compared_value }}.',
    'The image ratio is too big ({{ ratio }}). Allowed maximum ratio is {{ max_ratio }}.' => 'Размера ове слике је превелика ({{ ratio }}). Максимална дозвољена размера је {{ max_ratio }}.',
    'The image ratio is too small ({{ ratio }}). Minimum ratio expected is {{ min_ratio }}.' => 'Размера ове слике је премала ({{ ratio }}). Минимална очекивана размера је {{ min_ratio }}.',
    'The image is square ({{ width }}x{{ height }}px). Square images are not allowed.' => 'Слика је квадратна ({{ width }}x{{ height }}px). Квадратне слике нису дозвољене.',
    'The image is landscape oriented ({{ width }}x{{ height }}px). Landscape oriented images are not allowed.' => 'Слика је оријентације пејзажа ({{ width }}x{{ height }}px). Пејзажна оријентација слика није дозвољена.',
    'The image is portrait oriented ({{ width }}x{{ height }}px). Portrait oriented images are not allowed.' => 'Слика је оријантације портрета ({{ width }}x{{ height }}px). Портретна оријентација слика није дозвољена.',
    'This form should not contain extra fields.' => 'Овај формулар не треба да садржи додатна поља.',
    'The uploaded file was too large. Please try to upload a smaller file.' => 'Отпремљена датотека је била превелика. Молим покушајте отпремање мање датотеке.',
    'The CSRF token is invalid. Please try to resubmit the form.' => 'CSRF вредност је невалидна. Покушајте поново.',
  ),
  'security' => 
  array (
    'An authentication exception occurred.' => 'Изузетак при аутентификацији.',
    'Authentication credentials could not be found.' => 'Аутентификациони подаци нису пронађени.',
    'Authentication request could not be processed due to a system problem.' => 'Захтев за аутентификацију не може бити обрађен због системских проблема.',
    'Invalid credentials.' => 'Невалидни подаци за аутентификацију.',
    'Cookie has already been used by someone else.' => 'Колачић је већ искоришћен од стране неког другог.',
    'Not privileged to request the resource.' => 'Немате права приступа овом ресурсу.',
    'Invalid CSRF token.' => 'Невалидан CSRF токен.',
    'Digest nonce has expired.' => 'Време криптографског кључа је истекло.',
    'No authentication provider found to support the authentication token.' => 'Аутентификациони провајдер за подршку токена није пронађен.',
    'No session available, it either timed out or cookies are not enabled.' => 'Сесија није доступна, истекла је или су колачићи искључени.',
    'No token could be found.' => 'Токен не може бити пронађен.',
    'Username could not be found.' => 'Корисничко име не може бити пронађено.',
    'Account has expired.' => 'Налог је истекао.',
    'Credentials have expired.' => 'Подаци за аутентификацију су истекли.',
    'Account is disabled.' => 'Налог је онемогућен.',
    'Account is locked.' => 'Налог је закључан.',
  ),
));

$catalogueSr = new MessageCatalogue('sr', array (
));
$catalogue->addFallbackCatalogue($catalogueSr);
$catalogueEn = new MessageCatalogue('en', array (
  'validators' => 
  array (
    'This value should be false.' => 'This value should be false.',
    'This value should be true.' => 'This value should be true.',
    'This value should be of type {{ type }}.' => 'This value should be of type {{ type }}.',
    'This value should be blank.' => 'This value should be blank.',
    'The value you selected is not a valid choice.' => 'The value you selected is not a valid choice.',
    'You must select at least {{ limit }} choice.|You must select at least {{ limit }} choices.' => 'You must select at least {{ limit }} choice.|You must select at least {{ limit }} choices.',
    'You must select at most {{ limit }} choice.|You must select at most {{ limit }} choices.' => 'You must select at most {{ limit }} choice.|You must select at most {{ limit }} choices.',
    'One or more of the given values is invalid.' => 'One or more of the given values is invalid.',
    'This field was not expected.' => 'This field was not expected.',
    'This field is missing.' => 'This field is missing.',
    'This value is not a valid date.' => 'This value is not a valid date.',
    'This value is not a valid datetime.' => 'This value is not a valid datetime.',
    'This value is not a valid email address.' => 'This value is not a valid email address.',
    'The file could not be found.' => 'The file could not be found.',
    'The file is not readable.' => 'The file is not readable.',
    'The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.' => 'The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.',
    'The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.' => 'The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.',
    'This value should be {{ limit }} or less.' => 'This value should be {{ limit }} or less.',
    'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.' => 'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
    'This value should be {{ limit }} or more.' => 'This value should be {{ limit }} or more.',
    'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.' => 'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.',
    'This value should not be blank.' => 'This value should not be blank.',
    'This value should not be null.' => 'This value should not be null.',
    'This value should be null.' => 'This value should be null.',
    'This value is not valid.' => 'This value is not valid.',
    'This value is not a valid time.' => 'This value is not a valid time.',
    'This value is not a valid URL.' => 'This value is not a valid URL.',
    'The two values should be equal.' => 'The two values should be equal.',
    'The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.' => 'The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.',
    'The file is too large.' => 'The file is too large.',
    'The file could not be uploaded.' => 'The file could not be uploaded.',
    'This value should be a valid number.' => 'This value should be a valid number.',
    'This file is not a valid image.' => 'This file is not a valid image.',
    'This is not a valid IP address.' => 'This is not a valid IP address.',
    'This value is not a valid language.' => 'This value is not a valid language.',
    'This value is not a valid locale.' => 'This value is not a valid locale.',
    'This value is not a valid country.' => 'This value is not a valid country.',
    'This value is already used.' => 'This value is already used.',
    'The size of the image could not be detected.' => 'The size of the image could not be detected.',
    'The image width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px.' => 'The image width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px.',
    'The image width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px.' => 'The image width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px.',
    'The image height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px.' => 'The image height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px.',
    'The image height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px.' => 'The image height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px.',
    'This value should be the user\'s current password.' => 'This value should be the user\'s current password.',
    'This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.' => 'This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.',
    'The file was only partially uploaded.' => 'The file was only partially uploaded.',
    'No file was uploaded.' => 'No file was uploaded.',
    'No temporary folder was configured in php.ini.' => 'No temporary folder was configured in php.ini, or the configured folder does not exist.',
    'Cannot write temporary file to disk.' => 'Cannot write temporary file to disk.',
    'A PHP extension caused the upload to fail.' => 'A PHP extension caused the upload to fail.',
    'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.' => 'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.',
    'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.' => 'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.',
    'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.' => 'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.',
    'Invalid card number.' => 'Invalid card number.',
    'Unsupported card type or invalid card number.' => 'Unsupported card type or invalid card number.',
    'This is not a valid International Bank Account Number (IBAN).' => 'This is not a valid International Bank Account Number (IBAN).',
    'This value is not a valid ISBN-10.' => 'This value is not a valid ISBN-10.',
    'This value is not a valid ISBN-13.' => 'This value is not a valid ISBN-13.',
    'This value is neither a valid ISBN-10 nor a valid ISBN-13.' => 'This value is neither a valid ISBN-10 nor a valid ISBN-13.',
    'This value is not a valid ISSN.' => 'This value is not a valid ISSN.',
    'This value is not a valid currency.' => 'This value is not a valid currency.',
    'This value should be equal to {{ compared_value }}.' => 'This value should be equal to {{ compared_value }}.',
    'This value should be greater than {{ compared_value }}.' => 'This value should be greater than {{ compared_value }}.',
    'This value should be greater than or equal to {{ compared_value }}.' => 'This value should be greater than or equal to {{ compared_value }}.',
    'This value should be identical to {{ compared_value_type }} {{ compared_value }}.' => 'This value should be identical to {{ compared_value_type }} {{ compared_value }}.',
    'This value should be less than {{ compared_value }}.' => 'This value should be less than {{ compared_value }}.',
    'This value should be less than or equal to {{ compared_value }}.' => 'This value should be less than or equal to {{ compared_value }}.',
    'This value should not be equal to {{ compared_value }}.' => 'This value should not be equal to {{ compared_value }}.',
    'This value should not be identical to {{ compared_value_type }} {{ compared_value }}.' => 'This value should not be identical to {{ compared_value_type }} {{ compared_value }}.',
    'The image ratio is too big ({{ ratio }}). Allowed maximum ratio is {{ max_ratio }}.' => 'The image ratio is too big ({{ ratio }}). Allowed maximum ratio is {{ max_ratio }}.',
    'The image ratio is too small ({{ ratio }}). Minimum ratio expected is {{ min_ratio }}.' => 'The image ratio is too small ({{ ratio }}). Minimum ratio expected is {{ min_ratio }}.',
    'The image is square ({{ width }}x{{ height }}px). Square images are not allowed.' => 'The image is square ({{ width }}x{{ height }}px). Square images are not allowed.',
    'The image is landscape oriented ({{ width }}x{{ height }}px). Landscape oriented images are not allowed.' => 'The image is landscape oriented ({{ width }}x{{ height }}px). Landscape oriented images are not allowed.',
    'The image is portrait oriented ({{ width }}x{{ height }}px). Portrait oriented images are not allowed.' => 'The image is portrait oriented ({{ width }}x{{ height }}px). Portrait oriented images are not allowed.',
    'An empty file is not allowed.' => 'An empty file is not allowed.',
    'The host could not be resolved.' => 'The host could not be resolved.',
    'This value does not match the expected {{ charset }} charset.' => 'This value does not match the expected {{ charset }} charset.',
    'This is not a valid Business Identifier Code (BIC).' => 'This is not a valid Business Identifier Code (BIC).',
    'This form should not contain extra fields.' => 'This form should not contain extra fields.',
    'The uploaded file was too large. Please try to upload a smaller file.' => 'The uploaded file was too large. Please try to upload a smaller file.',
    'The CSRF token is invalid. Please try to resubmit the form.' => 'The CSRF token is invalid. Please try to resubmit the form.',
  ),
  'security' => 
  array (
    'An authentication exception occurred.' => 'An authentication exception occurred.',
    'Authentication credentials could not be found.' => 'Authentication credentials could not be found.',
    'Authentication request could not be processed due to a system problem.' => 'Authentication request could not be processed due to a system problem.',
    'Invalid credentials.' => 'Invalid credentials.',
    'Cookie has already been used by someone else.' => 'Cookie has already been used by someone else.',
    'Not privileged to request the resource.' => 'Not privileged to request the resource.',
    'Invalid CSRF token.' => 'Invalid CSRF token.',
    'Digest nonce has expired.' => 'Digest nonce has expired.',
    'No authentication provider found to support the authentication token.' => 'No authentication provider found to support the authentication token.',
    'No session available, it either timed out or cookies are not enabled.' => 'No session available, it either timed out or cookies are not enabled.',
    'No token could be found.' => 'No token could be found.',
    'Username could not be found.' => 'Username could not be found.',
    'Account has expired.' => 'Account has expired.',
    'Credentials have expired.' => 'Credentials have expired.',
    'Account is disabled.' => 'Account is disabled.',
    'Account is locked.' => 'Account is locked.',
  ),
  'SonataCoreBundle' => 
  array (
    'link_add' => 'Add new',
    'label_type_yes' => 'yes',
    'label_type_no' => 'no',
    'sonata_core_template_box_file_found_in' => 'This file can be found in',
    'label_type_equals' => 'is equal to',
    'label_type_not_equals' => 'is not equal to',
    'date_range_start' => 'From',
    'date_range_end' => 'To',
    'aliceblue' => 'AliceBlue',
    'antiquewhite' => 'AntiqueWhite',
    'aqua' => 'Aqua',
    'aquamarine' => 'Aquamarine',
    'azure' => 'Azure',
    'beige' => 'Beige',
    'bisque' => 'Bisque',
    'black' => 'Black',
    'blanchedalmond' => 'BlanchedAlmond',
    'blue' => 'Blue',
    'blueviolet' => 'BlueViolet',
    'brown' => 'Brown',
    'burlywood' => 'BurlyWood',
    'cadetblue' => 'CadetBlue',
    'chartreuse' => 'Chartreuse',
    'chocolate' => 'Chocolate',
    'coral' => 'Coral',
    'cornflowerblue' => 'CornflowerBlue',
    'cornsilk' => 'Cornsilk',
    'crimson' => 'Crimson',
    'cyan' => 'Cyan',
    'darkblue' => 'DarkBlue',
    'darkcyan' => 'DarkCyan',
    'darkgoldenrod' => 'DarkGoldenRod',
    'darkgray' => 'DarkGray',
    'darkgreen' => 'DarkGreen',
    'darkkhaki' => 'DarkKhaki',
    'darkmagenta' => 'DarkMagenta',
    'darkolivegreen' => 'DarkOliveGreen',
    'darkorange' => 'DarkOrange',
    'darkorchid' => 'DarkOrchid',
    'darkred' => 'DarkRed',
    'darksalmon' => 'DarkSalmon',
    'darkseagreen' => 'DarkSeaGreen',
    'darkslateblue' => 'DarkSlateBlue',
    'darkslategray' => 'DarkSlateGray',
    'darkturquoise' => 'DarkTurquoise',
    'darkviolet' => 'DarkViolet',
    'deeppink' => 'DeepPink',
    'deepskyblue' => 'DeepSkyBlue',
    'dimgray' => 'DimGray',
    'dodgerblue' => 'DodgerBlue',
    'firebrick' => 'FireBrick',
    'floralwhite' => 'FloralWhite',
    'forestgreen' => 'ForestGreen',
    'fuchsia' => 'Fuchsia',
    'gainsboro' => 'Gainsboro',
    'ghostwhite' => 'GhostWhite',
    'gold' => 'Gold',
    'goldenrod' => 'GoldenRod',
    'gray' => 'Gray',
    'green' => 'Green',
    'greenyellow' => 'GreenYellow',
    'honeydew' => 'HoneyDew',
    'hotpink' => 'HotPink',
    'indianred' => 'IndianRed',
    'indigo' => 'Indigo',
    'ivory' => 'Ivory',
    'khaki' => 'Khaki',
    'lavender' => 'Lavender',
    'lavenderblush' => 'LavenderBlush',
    'lawngreen' => 'LawnGreen',
    'lemonchiffon' => 'LemonChiffon',
    'lightblue' => 'LightBlue',
    'lightcoral' => 'LightCoral',
    'lightcyan' => 'LightCyan',
    'lightgoldenrodyellow' => 'LightGoldenRodYellow',
    'lightgray' => 'LightGray',
    'lightgreen' => 'LightGreen',
    'lightpink' => 'LightPink',
    'lightsalmon' => 'LightSalmon',
    'lightseagreen' => 'LightSeaGreen',
    'lightskyblue' => 'LightSkyBlue',
    'lightslategray' => 'LightSlateGray',
    'lightsteelblue' => 'LightSteelBlue',
    'lightyellow' => 'LightYellow',
    'lime' => 'Lime',
    'limegreen' => 'LimeGreen',
    'linen' => 'Linen',
    'magenta' => 'Magenta',
    'maroon' => 'Maroon',
    'mediumaquamarine' => 'MediumAquaMarine',
    'mediumblue' => 'MediumBlue',
    'mediumorchid' => 'MediumOrchid',
    'mediumpurple' => 'MediumPurple',
    'mediumseagreen' => 'MediumSeaGreen',
    'mediumslateblue' => 'MediumSlateBlue',
    'mediumspringgreen' => 'MediumSpringGreen',
    'mediumturquoise' => 'MediumTurquoise',
    'mediumvioletred' => 'MediumVioletRed',
    'midnightblue' => 'MidnightBlue',
    'mintcream' => 'MintCream',
    'mistyrose' => 'MistyRose',
    'moccasin' => 'Moccasin',
    'navajowhite' => 'NavajoWhite',
    'navy' => 'Navy',
    'oldlace' => 'OldLace',
    'olive' => 'Olive',
    'olivedrab' => 'OliveDrab',
    'orange' => 'Orange',
    'orangered' => 'OrangeRed',
    'orchid' => 'Orchid',
    'palegoldenrod' => 'PaleGoldenRod',
    'palegreen' => 'PaleGreen',
    'paleturquoise' => 'PaleTurquoise',
    'palevioletred' => 'PaleVioletRed',
    'papayawhip' => 'PapayaWhip',
    'peachpuff' => 'PeachPuff',
    'peru' => 'Peru',
    'pink' => 'Pink',
    'plum' => 'Plum',
    'powderblue' => 'PowderBlue',
    'purple' => 'Purple',
    'rebeccapurple' => 'RebeccaPurple',
    'red' => 'Red',
    'rosybrown' => 'RosyBrown',
    'royalblue' => 'RoyalBlue',
    'saddlebrown' => 'SaddleBrown',
    'salmon' => 'Salmon',
    'sandybrown' => 'SandyBrown',
    'seagreen' => 'SeaGreen',
    'seashell' => 'SeaShell',
    'sienna' => 'Sienna',
    'silver' => 'Silver',
    'skyblue' => 'SkyBlue',
    'slateblue' => 'SlateBlue',
    'slategray' => 'SlateGray',
    'snow' => 'Snow',
    'springgreen' => 'SpringGreen',
    'steelblue' => 'SteelBlue',
    'tan' => 'Tan',
    'teal' => 'Teal',
    'thistle' => 'Thistle',
    'tomato' => 'Tomato',
    'turquoise' => 'Turquoise',
    'violet' => 'Violet',
    'wheat' => 'Wheat',
    'white' => 'White',
    'whitesmoke' => 'WhiteSmoke',
    'yellow' => 'Yellow',
    'yellowgreen' => 'YellowGreen',
  ),
  'SonataBlockBundle' => 
  array (
    'sonata.block.container' => 'Container',
    'sonata.block.text' => 'Simple text',
    'sonata.block.rss' => 'RSS feed',
    'sonata.block.menu' => 'Menu',
    'sonata.block.template' => 'Template',
  ),
  'SonataAdminBundle' => 
  array (
    'sonata_administration' => 'Administration',
    'action_delete' => 'Delete',
    'btn_batch' => 'OK',
    'btn_create' => 'Create',
    'btn_create_and_edit_again' => 'Create',
    'btn_create_and_create_a_new_one' => 'Create and add another',
    'btn_create_and_return_to_list' => 'Create and return to list',
    'btn_filter' => 'Filter',
    'btn_advanced_filters' => 'Advanced filters',
    'btn_update' => 'Update',
    'btn_update_and_edit_again' => 'Update',
    'btn_update_and_return_to_list' => 'Update and close',
    'link_delete' => 'Delete',
    'link_action_create' => 'Add new',
    'link_action_list' => 'Return to list',
    'link_action_show' => 'Show',
    'link_action_edit' => 'Edit',
    'link_add' => 'Add new',
    'link_list' => 'List',
    'link_reset_filter' => 'Reset',
    'title_create' => 'Create',
    'title_dashboard' => 'Dashboard',
    'title_edit' => 'Edit "%name%"',
    'title_list' => 'List',
    'link_next_pager' => 'Next',
    'link_previous_pager' => 'Previous',
    'link_first_pager' => 'First',
    'link_last_pager' => 'Last',
    'Admin' => 'Admin',
    'link_expand' => 'expand/collapse',
    'no_result' => 'No result',
    'confirm_msg' => 'Are you sure ?',
    'action_edit' => 'Edit',
    'action_show' => 'Show',
    'all_elements' => 'All elements',
    'flash_batch_empty' => 'Action aborted. No items were selected.',
    'flash_create_success' => 'Item "%name%" has been successfully created.',
    'flash_create_error' => 'An error has occurred during the creation of item "%name%".',
    'flash_edit_success' => 'Item "%name%" has been successfully updated.',
    'flash_edit_error' => 'An error has occurred during update of item "%name%".',
    'flash_lock_error' => 'Another user has modified item "%name%". Please %link_start%click here%link_end% to reload the page and apply the changes again.',
    'flash_batch_delete_success' => 'Selected items have been successfully deleted.',
    'flash_batch_delete_error' => 'An Error has occurred during selected items deletion.',
    'flash_delete_error' => 'An Error has occurred during deletion of item "%name%".',
    'flash_delete_success' => 'Item "%name%" has been deleted successfully.',
    'form_not_available' => 'Form is not available.',
    'breadcrumb.link_dashboard' => '<i class="fa fa-home"></i>',
    'title_delete' => 'Confirm deletion',
    'message_delete_confirmation' => 'Are you sure you want to delete the selected "%object%" element?',
    'btn_delete' => 'Yes, delete',
    'title_batch_confirmation' => 'Confirm batch action \'%action%\'',
    'message_batch_confirmation' => 'Are you sure you want to confirm this action and execute it for the selected element?|Are you sure you want to confirm this action and execute it for the %count% selected elements?',
    'message_batch_all_confirmation' => 'Are you sure you want to confirm this action and execute it for all the elements?',
    'btn_execute_batch_action' => 'Yes, execute',
    'label_type_yes' => 'yes',
    'label_type_no' => 'no',
    'label_type_contains' => 'contains',
    'label_type_not_contains' => 'does not contain',
    'label_type_equals' => 'is equal to',
    'label_type_not_equals' => 'is not equal to',
    'label_type_equal' => '=',
    'label_type_greater_equal' => '>=',
    'label_type_greater_than' => '>',
    'label_type_less_equal' => '<=',
    'label_type_less_than' => '<',
    'label_date_type_equal' => '=',
    'label_date_type_greater_equal' => '>=',
    'label_date_type_greater_than' => '>',
    'label_date_type_less_equal' => '<=',
    'label_date_type_less_than' => '<',
    'label_date_type_null' => 'is empty',
    'label_date_type_not_null' => 'is not empty',
    'label_date_type_between' => 'between',
    'label_date_type_not_between' => 'not between',
    'label_filters' => 'Filters',
    'delete_or' => 'or',
    'link_action_history' => 'Revisions',
    'td_action' => 'Action',
    'td_compare' => 'Compare',
    'td_revision' => 'Revisions',
    'td_timestamp' => 'Date',
    'td_username' => 'Author',
    'td_role' => 'Role',
    'label_view_revision' => 'View Revision',
    'label_compare_revision' => 'Compare revision',
    'list_results_count_prefix' => 'at least',
    'list_results_count' => '1 result|%count% results',
    'label_export_download' => 'Download',
    'export_format_json' => 'JSON',
    'export_format_xml' => 'XML',
    'export_format_csv' => 'CSV',
    'export_format_xls' => 'XLS',
    'loading_information' => 'Loading information…',
    'btn_preview' => 'Preview',
    'btn_preview_approve' => 'Approve',
    'btn_preview_decline' => 'Decline',
    'label_per_page' => 'Per page',
    'list_select' => 'Select',
    'confirm_exit' => 'You have unsaved changes.',
    'link_edit_acl' => 'Edit ACL',
    'btn_update_acl' => 'Update ACL',
    'flash_acl_edit_success' => 'ACL has been successfuly updated.',
    'link_action_acl' => 'ACL',
    'short_object_description_placeholder' => 'No selection',
    'title_search_results' => 'Search results: %query%',
    'search_placeholder' => 'Search',
    'no_results_found' => 'no result found',
    'add_new_entry' => 'add new entry',
    'link_actions' => 'Actions',
    'noscript_warning' => 'Javascript is disabled in your web browser. Some features will not work properly.',
    'message_form_group_empty' => 'No fields available.',
    'link_filters' => 'Filters',
    'stats_view_more' => 'View more',
    'title_select_subclass' => 'Select object type',
    'no_subclass_available' => 'No object types available',
    'label_unknown_user' => 'unknown',
    'read_more' => 'Read more',
    'read_less' => 'Close',
  ),
));
$catalogueSr->addFallbackCatalogue($catalogueEn);

return $catalogue;
