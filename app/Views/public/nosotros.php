<section class="section">
    <h2 class="section__title">Sobre nosotros</h2>
    <p class="section__subtitle">"La Armadura de Dios"</p>

    <div class="card" style="max-width:850px; margin:0 auto 32px; padding:32px; border-radius:var(--radius-lg);">
        <h3 style="margin-top:0; color:var(--color-primary); font-size:24px;">Nuestra Misión</h3>
        <p style="font-size:16px; line-height:1.7; margin-bottom: 24px;">
            <?= nl2br(e(config_db('mision', 'El Club Atlético Deportivo Acarigua nació en 2019...'))) ?>
        </p>

        <h3 style="color:var(--color-primary); font-size:24px;">Nuestra Visión</h3>
        <p style="font-size:16px; line-height:1.7; margin-bottom: 24px;">
            <?= nl2br(e(config_db('vision', 'Visión por defecto del club...'))) ?>
        </p>

        <h3 style="color:var(--color-primary); font-size:24px;">Requisitos de Inscripción</h3>
        <div style="background:var(--color-surface); padding:20px; border-left:4px solid var(--color-primary); border-radius:4px; font-size:16px; line-height:1.6;">
            <?= nl2br(e(config_db('requisitos_inscripcion', 'Requisitos pendientes de definir...'))) ?>
        </div>
    </div>
</section>
