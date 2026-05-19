        <!-- Modal: Editar Datos Personales -->
        <div id="modal-editar-basico" class="modal-overlay" style="display:none;">
            <form id="form-editar-basico" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" method="POST"
                enctype="multipart/form-data" class="modal-container" style="max-width: 600px;" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title"><i class="ph ph-user-circle"></i> Editar Datos BÃ¡sicos</h3>
                    <button type="button" class="modal-close" data-close-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div id="error-basico" class="alert alert-danger" style="display:none; margin-bottom: 16px;"></div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Nombres *</label>
                            <input type="text" name="nombre" class="form-control" value="<?= e($atleta['nombre']) ?>"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" name="apellido" class="form-control"
                                value="<?= e($atleta['apellido']) ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">CÃ©dula o Nro. de Folio</label>
                            <?php
                                $cedVal   = $atleta['cedula'];
                                $cedPref  = 'V';
                                $cedNum   = '';
                                if (!empty($cedVal)) {
                                    if (str_contains($cedVal, '-')) {
                                        [$cedPref, $cedNum] = explode('-', $cedVal, 2);
                                    } else {
                                        $firstChar = strtoupper($cedVal[0]);
                                        if (in_array($firstChar, ['V', 'E', 'F'])) {
                                            $cedPref = $firstChar;
                                            $cedNum = substr($cedVal, 1);
                                        } else {
                                            $cedNum = $cedVal;
                                        }
                                    }
                                }
                            ?>
                            <div class="phone-field" id="phone-wrap-cedula" style="display: flex; align-items: stretch; border: 1px solid var(--color-border); border-radius: var(--radius-sm); overflow: hidden; background: var(--color-surface); height: 44px;">
                                <select class="phone-prefix" id="cedula_prefix" aria-label="Prefijo" style="border: none; background: var(--color-surface-2); font-weight: 700; font-size: 13px; padding: 0 12px; cursor: pointer; border-right: 1px solid var(--color-border); color: var(--color-text); outline: none;">
                                    <option value="V" <?= $cedPref==='V'?'selected':'' ?>>V</option>
                                    <option value="E" <?= $cedPref==='E'?'selected':'' ?>>E</option>
                                    <option value="F" <?= $cedPref==='F'?'selected':'' ?>>F</option>
                                </select>
                                <span class="phone-sep" style="display: flex; align-items: center; color: var(--color-text-muted); margin: 0 4px; font-weight: 300;">-</span>
                                <input type="text" class="phone-number" id="cedula_number" style="flex: 1; border: none; background: transparent; padding: 0 12px; font-size: 14px; outline: none; color: var(--color-text);" maxlength="10" placeholder="12.345.678" value="<?= e($cedNum) ?>">
                                <input type="hidden" name="cedula" id="cedula" value="<?= e($cedVal) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">TelÃ©fono Personal</label>
                            <input type="text" name="telefono" class="form-control"
                                value="<?= e($atleta['telefono']) ?>" placeholder="Ej: 0414-1234567">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Estatus</label>
                            <select name="estatus" class="form-control">
                                <?php foreach (ESTATUS_ATLETA as $val => $lbl): ?>
                                    <option value="<?= $val ?>" <?= (int) $atleta['estatus'] === $val ? 'selected' : '' ?>>
                                        <?= e($lbl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">CategorÃ­a</label>
                            <select name="categoria_id" class="form-control">
                                <option value="">â Seleccionar â</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['categoria_id'] ?>"
                                        <?= (int) $atleta['categoria_id'] === (int) $cat['categoria_id'] ? 'selected' : '' ?>>
                                        <?= e($cat['nombre_categoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">PosiciÃ³n</label>
                            <select name="posicion_de_juego" class="form-control">
                                <option value="">â Seleccionar â</option>
                                <?php foreach ($posiciones as $pos): ?>
                                    <option value="<?= $pos['posicion_id'] ?>"
                                        <?= (int) $atleta['posicion_juego_id'] === (int) $pos['posicion_id'] ? 'selected' : '' ?>><?= e($pos['nombre_posicion']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Fecha Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" class="form-control"
                                value="<?= e($atleta['fecha_nac']) ?>" required
                                max="<?= date('Y-m-d', strtotime('-6 years')) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pierna Dominante</label>
                            <select name="pierna_dominante" class="form-control">
                                <option value="">â Seleccionar â</option>
                                <?php foreach (PIERNA_DOMINANTE as $p): ?>
                                    <option value="<?= $p ?>" <?= $atleta['pierna_dominante'] === $p ? 'selected' : '' ?>>
                                        <?= e(ucfirst($p)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>

        <!-- Modal: Editar Foto -->
        <div id="modal-editar-foto" class="modal-overlay" style="display:none;">
            <form id="form-editar-foto" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>" method="POST"
                enctype="multipart/form-data" class="modal-container" style="max-width: 400px;" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title"><i class="ph ph-camera"></i> Actualizar Foto</h3>
                    <button type="button" class="modal-close" data-close-modal>&times;</button>
                </div>
                <div class="modal-body" style="text-align: center;">
                    <div id="error-foto" class="alert alert-danger" style="display:none; margin-bottom: 16px;"></div>

                    <div
                        style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px; border: 3px solid var(--color-primary-light);">
                        <?php if (!empty($atleta['foto'])): ?>
                            <img src="<?= e(url($atleta['foto'])) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div
                                style="width:100%; height:100%; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-text-muted);">
                                <i class="ph ph-user" style="font-size: 64px;"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <div class="upload-zone" id="upload-zone-foto">
                            <input type="file" name="foto" id="input-foto-file" accept="image/*" style="display:none;">
                            <div class="upload-content">
                                <i class="ph ph-cloud-arrow-up"></i>
                                <p>Seleccionar nueva imagen</p>
                                <span id="foto-filename">Formatos: JPG, PNG</span>
                            </div>
                        </div>
                        <p style="font-size: 11px; color: var(--color-text-muted); margin-top: 8px;">Recomendado: Imagen
                            cuadrada (1:1).</p>
                    </div>

                    <?php if (!empty($atleta['foto'])): ?>
                        <div style="margin-top: 16px;">
                            <label
                                style="display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="eliminar_foto" value="1">
                                <span style="font-size: 14px; color: var(--color-danger);">Eliminar foto actual</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-upload"></i> Subir Foto</button>
                </div>
            </form>
        </div>

        <!-- Modal: Editar Representante -->
        <div id="modal-editar-representante" class="modal-overlay" style="display:none;">
            <form id="form-editar-representante" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>"
                method="POST" class="modal-container" style="max-width: 500px;" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title"><i class="ph ph-users"></i> Editar Representante</h3>
                    <button type="button" class="modal-close" data-close-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div id="error-representante" class="alert alert-danger" style="display:none; margin-bottom: 16px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="tutor_nombres" class="form-control"
                                value="<?= e($atleta['tutor_nombres']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="tutor_apellidos" class="form-control"
                                value="<?= e($atleta['tutor_apellidos']) ?>">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Parentesco</label>
                            <select name="tutor_relacion" class="form-control">
                                <?php foreach (TIPO_RELACION_REPRESENTANTE as $rel): ?>
                                    <option value="<?= $rel ?>" <?= $atleta['tutor_relacion'] === $rel ? 'selected' : '' ?>>
                                        <?= e(ucfirst($rel)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">CÃ©dula</label>
                            <?php
                                $tcedVal   = $atleta['tutor_cedula'];
                                $tcedPref  = 'V';
                                $tcedNum   = '';
                                if (!empty($tcedVal)) {
                                    if (str_contains($tcedVal, '-')) {
                                        [$tcedPref, $tcedNum] = explode('-', $tcedVal, 2);
                                    } else {
                                        $firstChar = strtoupper($tcedVal[0]);
                                        if (in_array($firstChar, ['V', 'E'])) {
                                            $tcedPref = $firstChar;
                                            $tcedNum = substr($tcedVal, 1);
                                        } else {
                                            $tcedNum = $tcedVal;
                                        }
                                    }
                                }
                            ?>
                            <div class="phone-field" id="phone-wrap-tutor_cedula" style="display: flex; align-items: stretch; border: 1px solid var(--color-border); border-radius: var(--radius-sm); overflow: hidden; background: var(--color-surface); height: 44px;">
                                <select class="phone-prefix" id="tutor_cedula_prefix" aria-label="Prefijo" style="border: none; background: var(--color-surface-2); font-weight: 700; font-size: 13px; padding: 0 12px; cursor: pointer; border-right: 1px solid var(--color-border); color: var(--color-text); outline: none;">
                                    <option value="V" <?= $tcedPref==='V'?'selected':'' ?>>V</option>
                                    <option value="E" <?= $tcedPref==='E'?'selected':'' ?>>E</option>
                                </select>
                                <span class="phone-sep" style="display: flex; align-items: center; color: var(--color-text-muted); margin: 0 4px; font-weight: 300;">-</span>
                                <input type="text" class="phone-number" id="tutor_cedula_number" style="flex: 1; border: none; background: transparent; padding: 0 12px; font-size: 14px; outline: none; color: var(--color-text);" maxlength="10" placeholder="12.345.678" value="<?= e($tcedNum) ?>">
                                <input type="hidden" name="tutor_cedula" id="tutor_cedula" value="<?= e($tcedVal) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">TelÃ©fono de Contacto</label>
                        <input type="text" name="tutor_telefono" class="form-control"
                            value="<?= e($atleta['tutor_telefono']) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar</button>
                </div>
            </form>
        </div>

        <!-- Modal: Editar DirecciÃ³n Detallada -->
        <div id="modal-editar-direccion" class="modal-overlay" style="display:none;">
            <form id="form-editar-direccion" action="<?= e(url("/admin/atletas/{$atleta['atleta_id']}")) ?>"
                method="POST" class="modal-container" style="max-width: 600px;" novalidate>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title"><i class="ph ph-map-pin"></i> Editar DirecciÃ³n Detallada</h3>
                    <button type="button" class="modal-close" data-close-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div id="error-direccion" class="alert alert-danger" style="display:none; margin-bottom: 16px;">
                    </div>

                    <input type="hidden" id="select-pais" value="<?= $paises[0]['id'] ?? 1 ?>">
                    <div style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select id="select-estado" class="form-control">
                                <option value="">â Seleccionar â</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Municipio</label>
                            <select id="select-municipio" class="form-control">
                                <option value="">â Seleccionar â</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Parroquia *</label>
                            <select name="parroquia_id" id="select-parroquia" class="form-control" required>
                                <option value="">â Seleccionar â</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Localidad / Sector</label>
                            <input type="text" name="localidad" class="form-control"
                                value="<?= e($atleta['localidad']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de Vivienda</label>
                            <select name="tipo_vivienda" class="form-control">
                                <option value="casa" <?= ($atleta['tipo_vivienda'] ?? '') === 'casa' ? 'selected' : '' ?>>Casa</option>
                                <option value="apto" <?= ($atleta['tipo_vivienda'] ?? '') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                                <option value="edificio" <?= ($atleta['tipo_vivienda'] ?? '') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">UbicaciÃ³n EspecÃ­fica (Calle, Nro...)</label>
                        <textarea name="ubicacion_vivienda" class="form-control"
                            rows="2"><?= e($atleta['ubicacion_vivienda']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar</button>
                </div>
            </form>
        </div>
