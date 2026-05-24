<section class="section">
    <h2 class="section__title">Contacto</h2>
    <p class="section__subtitle">Comunícate con la directiva del club</p>

    <div style="max-width:800px; margin:0 auto;">
        
        <!-- Tarjetas de Información de Contacto -->
        <div class="feature-grid" style="margin-bottom: 40px;">
            <?php if ($correo = config_db('correo_contacto')): ?>
                <a href="mailto:<?= e($correo) ?>" class="feature-card" style="text-decoration:none; cursor:pointer;">
                    <div class="feature-card__icon" style="background:#FEE2E2; color:#EF4444;">
                        <i class="ph ph-envelope-simple" style="font-size:24px;"></i>
                    </div>
                    <h3>Correo Electrónico</h3>
                    <p><?= e($correo) ?></p>
                </a>
            <?php endif; ?>

            <?php if ($whatsapp = config_db('telefono_whatsapp')): ?>
                <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $whatsapp)) ?>" target="_blank" class="feature-card" style="text-decoration:none; cursor:pointer;">
                    <div class="feature-card__icon" style="background:#DCFCE7; color:#22C55E;">
                        <i class="ph ph-whatsapp-logo" style="font-size:24px;"></i>
                    </div>
                    <h3>WhatsApp</h3>
                    <p><?= e($whatsapp) ?></p>
                </a>
            <?php endif; ?>
        </div>

        <!-- Redes Sociales -->
        <div style="text-align:center; margin-bottom: 40px;">
            <h3 style="margin-bottom: 20px; font-size:24px;">Síguenos en nuestras redes</h3>
            <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
                <?php if ($facebook = config_db('facebook_url')): ?>
                    <a href="<?= e($facebook) ?>" target="_blank" class="btn btn-outline" style="border-color:#1877F2; color:#1877F2; display:inline-flex; align-items:center; gap:8px;">
                        <i class="ph ph-facebook-logo" style="font-size:18px;"></i> Facebook
                    </a>
                <?php endif; ?>
                
                <?php if ($instagram = config_db('instagram_url')): ?>
                    <a href="<?= e($instagram) ?>" target="_blank" class="btn btn-outline" style="border-color:#E1306C; color:#E1306C; display:inline-flex; align-items:center; gap:8px;">
                        <i class="ph ph-instagram-logo" style="font-size:18px;"></i> Instagram
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ubicación con foto -->
        <?php if ($maps = config_db('google_maps_url')): ?>
        <div class="card" style="padding:0; overflow:hidden; border-radius:var(--radius-lg); border:1px solid var(--color-border);">
            <img src="<?= e(asset('img/ubicacion.png')) ?>" alt="Ubicación del Club en Google Maps" 
                 style="width:100%; height:280px; object-fit:cover; display:block;">
            <div style="padding:24px; text-align:center;">
                <h3 style="margin:0 0 8px;">Nuestra Ubicación</h3>
                <p style="margin:0 0 16px; color:var(--color-text-muted);">
                    Av. Circunvalación Sur, diagonal a la Cruz Roja, Acarigua, Estado Portuguesa.
                </p>
                <a href="<?= e($maps) ?>" target="_blank" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
                    <i class="ph ph-map-pin" style="font-size:18px;"></i> Ver en Google Maps
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>
