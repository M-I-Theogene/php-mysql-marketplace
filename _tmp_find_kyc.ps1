$root = 'C:\xampp\htdocs\shopping'
Get-ChildItem -Path $root -Recurse -Include *.php -File -ErrorAction SilentlyContinue |
    Select-String -Pattern 'kycDocumentUrl|kyc-upload|id_document|kyc_' |
    ForEach-Object { '{0}:{1}: {2}' -f $_.Filename, $_.LineNumber, $_.Line.Trim() }
