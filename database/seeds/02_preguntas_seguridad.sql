-- Preguntas de seguridad estilo banca venezolana (10 preguntas, 4 grupos)
-- Requiere: ALTER TABLE preguntas_seguridad MODIFY preguntas VARCHAR(100) NOT NULL;
INSERT INTO `preguntas_seguridad` (`pregunta_id`, `preguntas`, `grupo`) VALUES
-- Grupo 1: Infancia y Lugares
(1, '¿Nombre de tu primera mascota?', 1),
(2, '¿Ciudad donde naciste?', 1),
(3, '¿Tu apodo de infancia?', 1),
-- Grupo 2: Preferencias Personales
(4, '¿Color favorito?', 2),
(5, '¿Tu plato de comida favorito?', 2),
(6, '¿Marca de tu primer teléfono?', 2),
-- Grupo 3: Familia y Amigos
(7, '¿Nombre de tu madre?', 3),
(8, '¿Nombre de tu mejor amigo?', 3),
-- Grupo 4: Deporte y Educación
(9, '¿Tu equipo de fútbol favorito?', 4),
(10, '¿Nombre de tu primera escuela?', 4);
