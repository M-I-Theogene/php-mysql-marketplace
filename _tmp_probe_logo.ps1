$ErrorActionPreference = 'Stop'
$root = 'c:\xampp\htdocs\shopping'
$logo = Join-Path $root 'assets\images\cypadi-logo-transparent.png'
$html = Join-Path $root '_tmp_doc1_kyc.html'

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

$doc = $word.Documents.Open($html, $false, $false)
Write-Output ('SECTIONS=' + $doc.Sections.Count)

$hdr = $doc.Sections.Item(1).Headers.Item(1)
$hdr.LinkToPrevious = $false
$hdr.Range.Text = ''
Write-Output ('HEADER_RANGE_OK len=' + $hdr.Range.End)

$hdr.Range.Collapse(1) | Out-Null
try {
  $shape = $hdr.Range.InlineShapes.AddPicture($logo, $false, $true)
  Write-Output ('ADDPICTURE_OK type=' + $shape.Type)
  try { Write-Output ('W=' + $shape.Width + ' H=' + $shape.Height) } catch { Write-Output ('READ_W_FAIL=' + $_.Exception.Message) }
  try { $shape.LockAspectRatio = -1; Write-Output 'LOCK_OK' } catch { Write-Output ('LOCK_FAIL=' + $_.Exception.Message) }
  try { $shape.Width = 74; Write-Output 'SET_W_OK' } catch { Write-Output ('SET_W_FAIL=' + $_.Exception.Message + ' HR=' + $_.Exception.HResult) }
  try { $shape.ScaleWidth = 25; Write-Output 'SCALE_OK' } catch { Write-Output ('SCALE_FAIL=' + $_.Exception.Message) }
} catch {
  Write-Output ('ADDPICTURE_FAIL=' + $_.Exception.Message)
}

$count = $doc.StoryRanges.Count
Write-Output ('STORIES=' + $count)

$doc.Close(0)
$word.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
Write-Output 'PROBE_LOGO_DONE'
