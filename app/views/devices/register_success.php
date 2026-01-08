<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<div class="container-lg mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white text-center py-3">
                    <h3 class="mb-0">✅ Device Successfully Registered!</h3>
                </div>
                <div class="card-body text-center p-4">
                    <div class="alert alert-info mb-4">
                        <h5 class="mb-3">🔐 Your Secure Device Key</h5>
                        <div class="input-group mb-2">
                            <input 
                                type="text" 
                                id="deviceKeyInput" 
                                class="form-control fw-bold text-monospace" 
                                value="<?= htmlspecialchars($deviceKey) ?>" 
                                readonly
                                style="font-size: 1.1rem; letter-spacing: 1px;"
                            >
                            <button class="btn btn-outline-secondary" type="button" onclick="copyDeviceKey()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <p class="text-muted small mt-2">
                            Provide this key to your device firmware or configuration.  
                            It will use this key to authenticate when sending data to the server.
                        </p>
                    </div>

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="/device" class="btn btn-primary">View All Devices</a>
                        <a href="/device/register" class="btn btn-outline-secondary">Register Another</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyDeviceKey() {
    const input = document.getElementById('deviceKeyInput');
    input.select();
    input.setSelectionRange(0, 99999); // For mobile
    document.execCommand('copy');
    
    // Show feedback
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => {
        btn.innerHTML = originalText;
    }, 2000);
}
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>