<?php

return [
  'informations' =>
  [
    'documentation' => 'https://docs.thirtybees.com/',
    'forum' => 'https://forum.thirtybees.com/',
    'blog' => 'https://thirtybees.com/blog/',
    'support' => 'https://forum.thirtybees.com/',
    'tailored_help' => 'https://store.thirtybees.com/services',
  ],
  'translations' =>
  [
    'Cannot create image "%1$s" for entity "%2$s"' => '無法建立圖片 "%s"，於 "%2$s"',
    'Cannot create image "%1$s" (bad permissions on folder "%2$s")' => '無法建立圖片 "%s"（資料夾 "%2$s" 權限有誤）',
    'Cannot create image "%s"' => '無法建立圖片 "%s"',
    'An SQL error occurred for entity <i>%1$s</i>: <i>%2$s</i>' => '所輸入的 <i>%1$s</i>: <i>%2$s</i> 發生了SQL錯誤',
    'SQL error on query <i>%s</i>' => 'SQL查詢錯誤 <i>%s</i>',
    '%s Login information' => '%s 登入信息',
    'Field required' => '必填欄位',
    'Invalid shop name' => '商店名稱有誤',
    'The field %s is limited to %d characters' => '欄位 %s 限制長度為 %s 字元',
    'Your firstname contains some invalid characters' => '您的姓名包含錯誤字元',
    'Your lastname contains some invalid characters' => '您的姓氏包含錯誤字元',
    'The password is incorrect (alphanumeric string with at least 8 characters)' => '密碼有誤（只接受英文字母與數字，且至少 8 個字）',
    'Password and its confirmation are different' => '密碼與確認不同',
    'This e-mail address is invalid' => '電郵地址無效 ',
    'Lingerie and Adult' => '內衣與成人用品',
    'Animals and Pets' => '動物與寵物',
    'Art and Culture' => '藝術與文化',
    'Babies' => '嬰幼兒用品',
    'Beauty and Personal Care' => '美容與個人護理',
    'Cars' => '汽車',
    'Computer Hardware and Software' => '電腦軟硬體',
    'Download' => '下載',
    'Fashion and accessories' => '時裝與配件',
    'Flowers, Gifts and Crafts' => '花店、禮品與工藝品',
    'Food and beverage' => '飲食',
    'HiFi, Photo and Video' => '影音',
    'Home and Garden' => '家居園藝',
    'Home Appliances' => '家用電器',
    'Jewelry' => '珠寶',
    'Mobile and Telecom' => '行動設備與通訊',
    'Services' => '服務',
    'Shoes and accessories' => '鞋子與配件',
    'Sports and Entertainment' => '運動與娛樂',
    'Travel' => '旅遊',
    'Database is connected' => '資料庫已經連結',
    'Database is created' => '資料庫已經建立',
    'Cannot create the database automatically' => '無法自動建立資料庫',
    'Create settings.inc file' => '建立 settings.inc 檔案',
    'Create database tables' => '建立資料表',
    'Create default shop and languages' => '建立預設商店及語言',
    'Populate database tables' => '佈署資料表',
    'Configure shop information' => '設定商店資訊',
    'Install demonstration data' => '安裝展示資料',
    'Install modules' => '安裝模組',
    'Install theme' => '安裝佈景',
    'Required PHP parameters' => '必填PHP參數',
    'The PHP bcmath extension is not enabled' => 'The PHP bcmath extension is not enabled',
    'GD library is not installed' => '沒有安裝 GD 函式庫',
    'The PHP json extension is not enabled' => 'The PHP json extension is not enabled',
    'PDO MySQL extension is not loaded' => '沒有載入 PDO MySQL 外掛',
    'PHP 5.6.0 or later is not enabled' => 'PHP 5.6.0 or later is not enabled',
    'Max execution time is lower than 30' => 'Max execution time is lower than 30',
    'Cannot create new files and folders' => '無法建立新檔案與資料夾',
    'Cannot upload files' => '無法上傳檔案',
    'The PHP xml extension is not enabled' => 'The PHP xml extension is not enabled',
    'The PHP zip extension/functionality is not enabled' => 'The PHP zip extension/functionality is not enabled',
    'Files' => '檔案',
    'Not all files were successfully uploaded on your server' => '未能成功將所有檔案上傳於你的伺服器',
    'Permissions on files and folders' => '文件和文件夾的​​權限',
    'Recursive write permissions for %1$s user on %2$s' => '％1$s 在 ％2$s 的用戶遞歸寫權限',
    'Recommended PHP parameters' => '推薦PHP參數',
    'You are using PHP %s version. The next minor version of thirty bees (1.1.0) will require PHP 5.6. To make sure you’re ready for the future, we recommend you to upgrade to PHP 5.6 now!' => 'You are using PHP %s version. The next minor version of thirty bees (1.1.0) will require PHP 5.6. To make sure you’re ready for the future, we recommend you to upgrade to PHP 5.6 now!',
    'PHP register_globals option is enabled' => 'PHP register_globals 選項啟用中',
    'GZIP compression is not activated' => '沒有啟用 GZIP 壓縮',
    'Mbstring extension is not enabled' => '沒有啟用 Mbstring 外掛',
    'Could not make a secure connection with PayPal. Your store might not be able to process payments.' => 'Could not make a secure connection with PayPal. Your store might not be able to process payments.',
    'Server name is not valid' => '伺服器名稱有誤',
    'You must enter a database name' => '您必須輸入資料庫名稱',
    'You must enter a database login' => '您必須輸入資料庫帳號',
    'Tables prefix is invalid' => '資料表前綴有誤',
    'Cannot convert database data to utf-8' => '無法轉換資料到 UTF-8 ',
    'At least one table with same prefix was already found, please change your prefix or drop your database' => '發現使用同樣前綴的資料表存在，請修改前綴或是移除現有資料庫',
    'The values of auto_increment increment and offset must be set to 1' => '自動增量的值和抵銷必須設置為1',
    'Database Server is not found. Please verify the login, password and server fields' => '找不到資料庫伺服器，請確認帳號、密碼與伺服器欄位',
    'Connection to MySQL server succeeded, but database "%s" not found' => '成功連線伺服器，不過找不到資料庫 "%s"',
    'Attempt to create the database automatically' => '嘗試自動建立資料庫',
    '%s file is not writable (check permissions)' => '%s 檔案無法寫入（請檢查權限）',
    '%s folder is not writable (check permissions)' => '%s 資料夾無法寫入（請檢查權限）',
    'Cannot write settings file' => '無法寫入設定檔',
    'Your database does not seem to support the collation `utf8mb4_unicode_ci`. Make sure you are using at least MySQL 5.5.3 or MariaDB 5.5' => 'Your database does not seem to support the collation `utf8mb4_unicode_ci`. Make sure you are using at least MySQL 5.5.3 or MariaDB 5.5',
    'The InnoDB database engine does not seem to be available. If you are using a MySQL alternative, could you please open an issue on %s? Thank you!' => 'The InnoDB database engine does not seem to be available. If you are using a MySQL alternative, could you please open an issue on %s? Thank you!',
    'Database structure file not found' => '找不到資料庫結構檔案',
    'Cannot create group shop' => '無法建立商店群組',
    'Cannot create shop' => '無法建立商店',
    'Cannot create shop URL' => '無法建立商店網址',
    'File "language.xml" not found for language iso "%s"' => '找不到語言代碼 "%s" 的 "language.xml"',
    'File "language.xml" not valid for language iso "%s"' => '語言代碼 "%s" 的 "language.xml" 有誤',
    'Cannot install language "%s"' => '無法安裝語言 "%s"',
    'Cannot copy flag language "%s"' => '無法複製語言圖示 "%s" ',
    'Cannot create admin account' => '無法建立管理者',
    'Cannot install module "%s"' => '無法安裝模組 "%s" ',
    'Fixtures class "%s" not found' => '找不到測試資料物件 "%s"',
    '"%s" must be an instance of "InstallXmlLoader"' => '"%s" 必須是 "InstallXmlLoader" 的一個實例',
    'Information about your Store' => '您商店的資訊',
    'Shop name' => '商店名稱',
    'Main activity' => '主要活動',
    'Please choose your main activity' => '請選擇主要活動',
    'Other activity...' => '其他活動...',
    'Help us learn more about your store so we can offer you optimal guidance and the best features for your business!' => '協助我們學習您的商店運作，這樣一來我們就可以為您的生意提供更好的建議與功能！',
    'Install demo products' => '安裝展示用產品',
    'Yes' => '是',
    'No' => '否',
    'Demo products are a good way to learn how to use thirty bees. You should install them if you are not familiar with it.' => 'Demo products are a good way to learn how to use thirty bees. You should install them if you are not familiar with it.',
    'Country' => '國家',
    'Select your country' => '選擇您的國家',
    'Shop timezone' => '網店時區',
    'Select your timezone' => '選擇您的時區',
    'Your Account' => '您的帳號',
    'First name' => '名字',
    'Last name' => '姓氏',
    'E-mail address' => '電郵地址',
    'This email address will be your username to access your store\'s back office.' => '這個信箱會成為您登入商店後台的帳號',
    'Shop password' => '商店密碼',
    'Must be at least 8 characters' => '最少 8 個字元',
    'Re-type to confirm' => '再次輸入確認',
    'All information you give us is collected by us and is subject to data processing and statistics, it is necessary for the members of the thirty bees company in order to respond to your requests. Your personal data may be communicated to service providers and partners as part of partner relationships. Under the current "Act on Data Processing, Data Files and Individual Liberties" you have the right to access, rectify and oppose to the processing of your personal data through this <a href="%s" onclick="return !window.open(this.href)">link</a>.' => 'All information you give us is collected by us and is subject to data processing and statistics, it is necessary for the members of the thirty bees company in order to respond to your requests. Your personal data may be communicated to service providers and partners as part of partner relationships. Under the current "Act on Data Processing, Data Files and Individual Liberties" you have the right to access, rectify and oppose to the processing of your personal data through this <a href="%s" onclick="return !window.open(this.href)">link</a>.',
    'Configure your database by filling out the following fields' => '填寫下面欄位來設定資料庫',
    'To use thirty bees, you must create a database to collect all of your store\'s data-related activities.' => 'To use thirty bees, you must create a database to collect all of your store\'s data-related activities.',
    'Please complete the fields below in order for thirty bees to connect to your database. ' => 'Please complete the fields below in order for thirty bees to connect to your database. ',
    'Database server address' => '伺服器位址',
    'The default port is 3306. To use a different port, add the port number at the end of your server\'s address i.e ":4242".' => '預設連接埠是 3306 ，要使用其他連接埠，請將連接埠編號加入到伺服器網址後面，例如 "localhost:4242"',
    'Database name' => '資料庫名稱',
    'Database login' => '帳號',
    'Database password' => '密碼',
    'Tables prefix' => '資料表前綴',
    'Drop existing tables (mode dev)' => '移除現有資料表（開發用）',
    'Test your database connection now!' => '現在測試您的資料庫連線！',
    'Next' => '下一步',
    'Back' => '返回',
    'If you need some assistance, you can <a href="%1$s" onclick="return !window.open(this.href);">get tailored help</a> from our support team. <a href="%2$s" onclick="return !window.open(this.href);">The official documentation</a> is also here to guide you.' => 'If you need some assistance, you can <a href="%1$s" onclick="return !window.open(this.href);">get tailored help</a> from our support team. <a href="%2$s" onclick="return !window.open(this.href);">The official documentation</a> is also here to guide you.',
    'Official forum' => '討論區',
    'Support' => '支援',
    'Documentation' => '文件',
    'Contact us' => '聯絡我們',
    'thirty bees Installation Assistant' => 'thirty bees Installation Assistant',
    'Forum' => '討論區',
    'Blog' => '博客',
    'menu_welcome' => '選擇語言',
    'menu_license' => '授權聲明',
    'menu_system' => '系統兼容性',
    'menu_configure' => '商店資訊',
    'menu_database' => '系統配置',
    'menu_process' => '商店安裝',
    'Installation Assistant' => '安裝協助',
    'To install thirty bees, you need to have JavaScript enabled in your browser.' => 'To install thirty bees, you need to have JavaScript enabled in your browser.',
    'http://enable-javascript.com/' => 'http://enable-javascript.com/',
    'License Agreements' => '授權聲明',
    'To enjoy the many features that are offered for free by thirty bees, please read the license terms below. thirty bees core is licensed under OSL 3.0, while the modules and themes are licensed under AFL 3.0.' => 'To enjoy the many features that are offered for free by thirty bees, please read the license terms below. thirty bees core is licensed under OSL 3.0, while the modules and themes are licensed under AFL 3.0.',
    'I agree to the above terms and conditions.' => '我同意上面的規則與條件',
    'Done!' => '完成！',
    'An error occurred during installation...' => '安裝過程出現錯誤.....',
    'You can use the links on the left column to go back to the previous steps, or restart the installation process by <a href="%s">clicking here</a>.' => '您可以使用左邊的連結來回到上一步，或是 <a href="%s">點選這裡</a> 重新開始安裝程序',
    'Your installation is finished!' => '您的安裝已經完成！',
    'You have just finished installing your shop. Thank you for using thirty bees!' => 'You have just finished installing your shop. Thank you for using thirty bees!',
    'Please remember your login information:' => '請記住您的登入資訊 ：',
    'E-mail' => '電子信箱',
    'Print my login information' => '列印我的登入資訊',
    'Password' => '密碼',
    'Display' => '顯示',
    'For security purposes, you must delete the "install" folder.' => '基於安全考量，您必須刪除 install 資料夾',
    'Back Office' => '後台',
    'Manage your store using your Back Office. Manage your orders and customers, add modules, change themes, etc.' => '透過後台可以管理您的商店，包括訂單、客戶、模組與佈景等等',
    'Manage your store' => '管理您的商店',
    'Front Office' => '前台',
    'Discover your store as your future customers will see it!' => '瀏覽未來您的客戶會看到的商店畫面！',
    'Discover your store' => '瀏覽您的商店',
    'Share your experience with your friends!' => '和您的朋友分享經驗！',
    'I just built an online store with thirty bees!' => 'I just built an online store with thirty bees!',
    'Tweet' => 'Tweet',
    'Share' => '分享',
    'Pinterest' => 'Pinterest',
    'LinkedIn' => 'LinkedIn',
    'We are currently checking thirty bees compatibility with your system environment' => 'We are currently checking thirty bees compatibility with your system environment',
    'If you have any questions, please visit our <a href="%1$s" target="_blank">documentation</a> and <a href="%2$s" target="_blank">community forum</a>.' => '如果有任何問題，請瀏覽我們的 <a href="%1$s" target="_blank">文件</a> 與 <a href="%2$s" target="_blank">討論區</a>',
    'thirty bees compatibility with your system environment has been verified!' => 'thirty bees compatibility with your system environment has been verified!',
    'Oops! Please correct the item(s) below, and then click "Refresh information" to test the compatibility of your new system.' => '請修正下面項目，然後點選 "重新整理資訊" 來測試系統相容性',
    'Refresh these settings' => '重新整理這些設定',
    'thirty bees requires at least 128 MiB of memory to run: please check the memory_limit directive in your php.ini file or contact your host provider about this.' => 'thirty bees requires at least 128 MiB of memory to run: please check the memory_limit directive in your php.ini file or contact your host provider about this.',
    '<b>Warning: You cannot use this tool to upgrade your store anymore.</b><br /><br />You already have <b>thirty bees version %1$s installed</b>.<br /><br />Use module Core Updater to update to the latest version.' => '<b>Warning: You cannot use this tool to upgrade your store anymore.</b><br /><br />You already have <b>thirty bees version %1$s installed</b>.<br /><br />Use module Core Updater to update to the latest version.',
    'Welcome to the thirty bees %s Installer' => 'Welcome to the thirty bees %s Installer',
    'Installing thirty bees is quick and easy. In just a few moments, you will become part of a community consisting of more than one merchant. You are on the way to creating your own unique online store that you can manage easily every day.' => 'Installing thirty bees is quick and easy. In just a few moments, you will become part of a community consisting of more than one merchant. You are on the way to creating your own unique online store that you can manage easily every day.',
    'Continue the installation in:' => '繼續安裝：',
    'The language selection above only applies to the Installation Assistant. Once your store is installed, you can choose the language of your store from over %d translations, all for free!' => '上面選擇的語言只用在安裝過程，一旦完成安裝，您可以為商店加入超過 %d 種翻譯，而且都是免費的！',
  ],
];
