<?php include __DIR__ . '/../layouts/html/header.php'; ?>

<style>
    .key-display {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        background-color: #f8f9fa;
        letter-spacing: 0.5px; /* Slightly reduced for narrow view */
    }
    .card-success-icon {
        font-size: 3rem;
        color: #198754;
        margin-bottom: 1rem;
    }
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
        <div class="col-md-6 col-lg-4"> 
            <div class="card shadow border-0">
                <div class="card-body p-4 text-center">
                    
                    <div class="card-success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <h3 class="fw-bold mb-2">Registered!</h3>
                    <p class="text-muted small mb-4">Device added successfully.</p>

                    <div class="p-3 border rounded-3 bg-light mb-4">
                        <label class="form-label fw-bold text-uppercase x-small text-muted" style="font-size: 0.7rem;">Secret Device Key</label>
                        <div class="input-group input-group-sm shadow-sm">
                            <input 
                                type="text" 
                                id="deviceKeyInput" 
                                class="form-control key-display border-0 text-center" 
                                value="<?= htmlspecialchars($deviceKey) ?>" 
                                readonly
                            >
                            <button class="btn btn-dark" type="button" id="copyBtn" onclick="copyDeviceKey()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="mt-2 py-2 px-2 bg-warning-subtle rounded-2 border border-warning-subtle">
                            <p class="text-warning-emphasis mb-0" style="font-size: 0.75rem;">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Save this key now.
                            </p>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/device" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>Go to Dashboard
                        </a>
                        <a href="/device/register" class="btn btn-outline-secondary btn-sm">
                            Register Another
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/docs/connection" class="text-decoration-none text-muted small">
                    <i class="fas fa-book me-1"></i> Connection Help
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// ... (Your existing script remains exactly the same) ...
async function copyDeviceKey() {
    const keyInput = document.getElementById('deviceKeyInput');
    const copyBtn = document.getElementById('copyBtn');
    const originalContent = copyBtn.innerHTML;
    try {
        await navigator.clipboard.writeText(keyInput.value);
        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        copyBtn.classList.replace('btn-dark', 'btn-success');
        setTimeout(() => {
            copyBtn.innerHTML = originalContent;
            copyBtn.classList.replace('btn-success', 'btn-dark');
        }, 2000);
    } catch (err) {
        keyInput.select();
        document.execCommand('copy');
    }
}
</script>

<?php include __DIR__ . '/../layouts/html/footer.php'; ?>