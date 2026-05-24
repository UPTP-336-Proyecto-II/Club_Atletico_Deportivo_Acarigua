<footer class="site-footer">
    <div style="max-width:1100px; margin:0 auto;">
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; gap:24px; text-align:left;">
            <!-- Info del club -->
            <div>
                <strong style="color:#fff; font-size:16px;">Club Atlético Deportivo Acarigua</strong>
                <div style="margin-top:4px;">Formando atletas con valores cristianos.</div>
                <div style="margin-top:4px; font-size:13px;">Av. Circunvalación Sur, diagonal a la Cruz Roja</div>
                <div style="font-size:13px;">Municipio Páez, Portuguesa, Venezuela · Fundado 2019</div>
            </div>
            <!-- Redes sociales -->
            <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <?php if ($fb = config_db('facebook_url')): ?>
                    <a href="<?= e($fb) ?>" target="_blank" title="Facebook" style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.1); color:#CBD5E1; transition:all 0.2s;">
                        <i class="ph ph-facebook-logo" style="font-size:18px;"></i>
                    </a>
                <?php endif; ?>
                <?php if ($ig = config_db('instagram_url')): ?>
                    <a href="<?= e($ig) ?>" target="_blank" title="Instagram" style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.1); color:#CBD5E1; transition:all 0.2s;">
                        <i class="ph ph-instagram-logo" style="font-size:18px;"></i>
                    </a>
                <?php endif; ?>
                <?php if ($wa = config_db('telefono_whatsapp')): ?>
                    <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $wa)) ?>" target="_blank" title="WhatsApp" style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.1); color:#CBD5E1; transition:all 0.2s;">
                        <i class="ph ph-whatsapp-logo" style="font-size:18px;"></i>
                    </a>
                <?php endif; ?>
                <?php if ($correo = config_db('correo_contacto')): ?>
                    <a href="mailto:<?= e($correo) ?>" title="Correo" style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.1); color:#CBD5E1; transition:all 0.2s;">
                        <i class="ph ph-envelope-simple" style="font-size:18px;"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <hr class="site-footer__divider">
    <div>&copy; <?= date('Y') ?> Club Atlético Deportivo Acarigua. Todos los derechos reservados.</div>
</footer>
