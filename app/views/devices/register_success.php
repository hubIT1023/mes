<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
    .key-display {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        background-color: #f8f9fa;
        letter-spacing: 1px;
    }
    .card-success-icon {
        font-size: 3rem;
        color: #198754;
        margin-bottom: 1rem;
    }
    /* Subtle entrance animation */
    .fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container-lg mt-5 fade-up">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow border-0">
                <div class="card-body p-5 text-center">
                    
                    <div class="card-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <h2 class="fw-bold mb-2">Device Registered!</h2>
                    <p class="text-muted mb-4">Your device has been added to the secure network.</p>

                    <div class="p-4 border rounded-3 bg-light mb-4">
                        <label class="form-label fw-bold text-uppercase small text-muted">Secret Device Key</label>
                        <div class="input-group shadow-sm">
                            <input 
                                type="text" 
                                id="deviceKeyInput" 
                                class="form-control form-control-lg key-display border-0 text-center" 
                                value="<?= htmlspecialchars($deviceKey) ?>" 
                                readonly
                            >
                            <button class="btn btn-dark px-4" type="button" id="copyBtn" onclick="copyDeviceKey()">
                                <i class="fas fa-copy me-1"></i> Copy
                            </button>
                        </div>
                        <div class="mt-3 py-2 px-3 bg-warning-subtle rounded-2 border border-warning-subtle">
                            <p class="text-warning-emphasis small mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <strong>Warning:</strong> Copy this key now. For security, we won't show it again in full.
                            </p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/device" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-list me-2"></i>Go to Dashboard
                        </a>
                        <a href="/device/register" class="btn btn-outline-secondary btn-lg px-4">
                            Register Another
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/docs/connection" class="text-decoration-none text-muted small">
                    <i class="fas fa-book me-1"></i> Need help connecting your device?
                </a>
            </div>
        </div>
    </div>
</div>

<script>
async function copyDeviceKey() {
    const keyInput = document.getElementById('deviceKeyInput');
    const copyBtn = document.getElementById('copyBtn');
    const originalContent = copyBtn.innerHTML;

    try {
        await navigator.clipboard.writeText(keyInput.value);
        
        // Visual Success Feedback
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        copyBtn.classList.replace('btn-dark', 'btn-success');
        keyInput.classList.add('is-valid');

        setTimeout(() => {
            copyBtn.innerHTML = originalContent;
            copyBtn.classList.replace('btn-success', 'btn-dark');
            keyInput.classList.remove('is-valid');
        }, 2500);
    } catch (err) {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        keyInput.select();
        document.execCommand('copy');
    }
}
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>