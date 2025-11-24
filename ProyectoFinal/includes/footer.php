<?php
// includes/footer.php
?>
    </main>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-4 border-top">
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="bi bi-house-heart-fill text-primary"></i> 
                        Sistema de Gestión Zoológico
                    </h5>
                    <p class="text-muted small">
                        Sistema integral de gestión para zoológicos con control de acceso basado en roles (RBAC).
                    </p>
                </div>
                
                <div class="col-md-3">
                    <h6 class="mb-3">Enlaces Rápidos</h6>
                    <ul class="list-unstyled">
                        <li>
                            <a href="/dawb/ProyectoFinal/public/index.php" class="text-muted text-decoration-none">
                                <i class="bi bi-house"></i> Inicio
                            </a>
                        </li>
                        <?php if (SessionManager::isLoggedIn()): ?>
                        <li>
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="bi bi-question-circle"></i> Ayuda
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="bi bi-file-earmark-text"></i> Documentación
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h6 class="mb-3">Información</h6>
                    <ul class="list-unstyled text-muted small">
                        <li>
                            <i class="bi bi-shield-check"></i> Conexión Segura
                        </li>
                        <li>
                            <i class="bi bi-clock"></i> 
                            <?php echo date('d/m/Y H:i:s'); ?>
                        </li>
                        <?php if (SessionManager::isLoggedIn()): ?>
                        <li>
                            <i class="bi bi-person-check"></i> 
                            Sesión activa
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <hr class="my-3">
            
            <div class="row">
                <div class="col-12 text-center text-muted small">
                    <p class="mb-0">
                        © <?php echo date('Y'); ?> Sistema Zoológico. Todos los derechos reservados.
                        <br>
                        <span class="badge bg-primary">v1.0.0</span>
                        <span class="badge bg-secondary">PHP <?php echo PHP_VERSION; ?></span>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/dawb/ProyectoFinal/public/js/main.js"></script>
    
    <script>
        // Auto-cerrar alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Confirmación antes de cerrar sesión
        document.querySelectorAll('a[href*="logout"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>