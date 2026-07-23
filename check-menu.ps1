try {
    $session = Invoke-WebRequest -Uri "http://localhost:8080/wp-login.php" -UseBasicParsing -TimeoutSec 10
    $loginData = @{
        log = "admin"
        pwd = "admin"
        wp-submit = "Log In"
        redirect_to = "/wp-admin/"
        testcookie = "1"
    }
    $login = Invoke-WebRequest -Uri "http://localhost:8080/wp-login.php" -Method Post -Body $loginData -UseBasicParsing -TimeoutSec 10 -SessionVariable ws
    
    $admin = Invoke-WebRequest -Uri "http://localhost:8080/wp-admin/" -WebSession $ws -UseBasicParsing -TimeoutSec 10
    
    if ($admin.Content -match "DShop") {
        Write-Output "DShop menu FOUND in admin panel"
    } else {
        Write-Output "DShop menu NOT found in admin panel"
    }
    
    if ($admin.Content -match "dshop") {
        Write-Output "dshop slug found"
    }
} catch {
    Write-Output "Error: $($_.Exception.Message)"
}
