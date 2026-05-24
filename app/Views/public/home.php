<section class="hero">
    <div class="hero__carousel" id="heroCarousel">
        <?php for ($i = 1; $i <= 7; $i++): ?>
            <div class="carousel-slide <?= $i === 1 ? 'active' : '' ?>" 
                 style="background-image: linear-gradient(rgba(220,38,38,0.65), rgba(17,24,39,0.85)), url('<?= e(asset('img/carrusel/' . $i . '.jpeg')) ?>');<?= $i === 2 ? ' background-position: center top;' : '' ?>">
            </div>
        <?php endfor; ?>
    </div>
    
    <div class="hero__content">
        <span class="hero__badge">Fundado 2019</span>
        <h1 class="hero__title">Club Atlético<br>Deportivo Acarigua</h1>
        <p class="hero__subtitle">
            Formando atletas con valores cristianos, disciplina y excelencia
            deportiva. Más de 250 personas beneficiadas en el municipio Páez.
        </p>
        <div class="hero__actions">
            <a href="<?= e(url('/login')) ?>" class="btn btn-primary btn-lg">Acceder al Sistema</a>
            <a href="<?= e(url('/nosotros')) ?>" class="btn btn-outline btn-lg">Conocer Más</a>
        </div>
    </div>
</section>

<style>
.hero__carousel {
    position: absolute;
    inset: 0;
    z-index: 1;
    overflow: hidden;
    background-color: #111;
}
.carousel-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
}
.carousel-slide.active {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.carousel-slide');
    let currentSlide = 0;
    
    if(slides.length > 0) {
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000); // Cambia de imagen cada 5 segundos
    }
});
</script>

<section class="section" id="caracteristicas">
    <h2 class="section__title">Gestión deportiva integral</h2>
    <p class="section__subtitle">
        Todas las herramientas necesarias para el seguimiento técnico, médico y antropométrico
        del rendimiento de cada atleta.
    </p>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-card__icon">👥</div>
            <h3>Gestión de Atletas</h3>
            <p>Registro detallado con información personal, técnica, médica, foto, tutor y dirección completa.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">📏</div>
            <h3>Antropometría</h3>
            <p>Seguimiento de peso, altura, envergadura e índices de masa corporal con gráficos de evolución.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">⚡</div>
            <h3>Rendimiento Físico</h3>
            <p>Tests de fuerza, resistencia, velocidad, coordinación y reacción con visualización en radar.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">🏥</div>
            <h3>Ficha Médica</h3>
            <p>Historial de salud, alergias, tipo sanguíneo, lesiones y condiciones relevantes.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">📋</div>
            <h3>Control de Asistencias</h3>
            <p>Control de asistencia diario por categoría con observaciones y estadísticas por atleta.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card__icon">📄</div>
            <h3>Reportes PDF</h3>
            <p>Generación automática de fichas técnicas individuales con datos, métricas y gráficos.</p>
        </div>
    </div>
</section>
