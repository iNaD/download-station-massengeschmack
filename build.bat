:: Delete old data
del massengeschmack.host
:: create the .tar.gz
7z a -ttar -so massengeschmack INFO massengeschmack.php | 7z a -si -tgzip massengeschmack.host
