$ErrorActionPreference = 'Stop'
$log = 'c:\xampp\htdocs\shopping\_tmp_word_test.log'
Remove-Item $log -ErrorAction SilentlyContinue
Add-Content $log ('t0 ' + (Get-Date -Format 'HH:mm:ss'))

$word = New-Object -ComObject Word.Application
Add-Content $log ('t1 instantiated ' + (Get-Date -Format 'HH:mm:ss'))
$word.Visible = $false
$word.DisplayAlerts = 0
Add-Content $log ('t2 version ' + $word.Version)
$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
Add-Content $log ('t3 done ' + (Get-Date -Format 'HH:mm:ss'))
Write-Output 'TEST_DONE'
