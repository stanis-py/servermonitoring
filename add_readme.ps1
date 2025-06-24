# PowerShell script to add README.md to git and push changes
Write-Host "Adding README.md to git..."
git add README.md

Write-Host "Committing changes..."
git commit -m "Add README.md with project documentation"

Write-Host "Pushing changes to remote repository..."
git push

Write-Host "Done!" 