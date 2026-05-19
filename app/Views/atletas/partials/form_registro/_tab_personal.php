            <div id="tab-personal" class="form-tab-panel active">
                <div class="af-section-header">
                    <div class="af-section-icon"><i class="ph ph-identification-card"></i></div>
                    <div class="af-section-info">
                        <h3>Información Básica</h3>
                        <p>Datos de identificación y contacto del deportista</p>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Nombres</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="50" value="<?= e($get('nombre')) ?>" placeholder="Ej: Juan Carlos">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Apellidos</label>
                        <input type="text" name="apellido" class="form-control" required maxlength="50" value="<?= e($get('apellido')) ?>" placeholder="Ej: Pérez Rodríguez">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label" id="label-cedula">Cédula o Cód. de Partida</label>
                        <?php
                            $cedVal   = $get('cedula');
                            $cedPref  = 'V';
                            $cedNum   = '';
                            if (!empty($cedVal)) {
                                if (str_contains($cedVal, '-')) {
                                    [$cedPref, $cedNum] = explode('-', $cedVal, 2);
                                } else {
                                    $firstChar = strtoupper($cedVal[0]);
                                    if (in_array($firstChar, ['V', 'E', 'P'])) {
                                        $cedPref = $firstChar;
                                        $cedNum = substr($cedVal, 1);
                                    } else {
                                        $cedNum = $cedVal;
                                    }
                                }
                            }
                        ?>
                        <div class="phone-field" id="phone-wrap-cedula">
                            <select class="phone-prefix" id="cedula_prefix" aria-label="Prefijo">
                                <option value="V" <?= $cedPref==='V'?'selected':'' ?>>V</option>
                                <option value="E" <?= $cedPref==='E'?'selected':'' ?>>E</option>
                                <option value="P" <?= $cedPref==='P'?'selected':'' ?>>P</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <!-- Input para Cédula -->
                            <input type="text" class="phone-number" id="cedula_number"
                                   maxlength="10" placeholder="12.345.678"
                                   autocomplete="off"
                                   value="<?= $cedPref !== 'P' ? e($cedNum) : '' ?>"
                                   <?= $cedPref === 'P' ? 'style="display:none;"' : '' ?>>
                                   
                            <!-- Inputs para Partida -->
                            <div id="folio_inputs" style="display: <?= $cedPref === 'P' ? 'flex' : 'none' ?>; flex: 1; align-items: center;">
                                <?php
                                    $fYear = ''; $fActa = ''; $fFolio = '';
                                    if ($cedPref === 'P') {
                                        $fParts = explode('-', $cedNum);
                                        if (count($fParts) === 3) {
                                            $fYear = $fParts[0]; $fActa = $fParts[1]; $fFolio = $fParts[2];
                                        } else {
                                            $fFolio = $cedNum;
                                        }
                                    }
                                ?>
                                <input type="text" id="folio_year" class="phone-number" style="width:45px; padding:0 4px; text-align:center;" placeholder="Año" maxlength="4" value="<?= e($fYear) ?>">
                                <span class="phone-sep">-</span>
                                <input type="text" id="folio_acta" class="phone-number" style="width:50px; padding:0 4px; text-align:center;" placeholder="Acta" maxlength="5" value="<?= e($fActa) ?>">
                                <span class="phone-sep">-</span>
                                <input type="text" id="folio_num" class="phone-number" style="min-width:0; flex:1; padding:0 4px; text-align:center;" placeholder="Folio" maxlength="5" value="<?= e($fFolio) ?>">
                            </div>
                            
                            <input type="hidden" name="cedula" id="cedula" value="<?= e($cedVal) ?>">
                        </div>
                        <span class="field-error" id="cedula-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="label-telefono">Tel&eacute;fono</label>
                        <?php
                            $telVal   = $get('telefono');
                            $telPref  = '';
                            $telNum   = '';
                            foreach (['0412','0414','0416','0422','0424','0426'] as $_p) {
                                if (str_starts_with($telVal, $_p)) { $telPref = $_p; $telNum = substr($telVal, 4); break; }
                            }
                        ?>
                        <div class="phone-field" id="phone-wrap-telefono">
                            <select class="phone-prefix" id="telefono_prefix" aria-label="Prefijo">
                                <option value="0412" <?= $telPref==='0412'?'selected':'' ?>>0412</option>
                                <option value="0414" <?= $telPref==='0414'?'selected':'' ?>>0414</option>
                                <option value="0416" <?= $telPref==='0416'?'selected':'' ?>>0416</option>
                                <option value="0422" <?= $telPref==='0422'?'selected':'' ?>>0422</option>
                                <option value="0424" <?= $telPref==='0424'?'selected':'' ?>>0424</option>
                                <option value="0426" <?= $telPref==='0426'?'selected':'' ?>>0426</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <input type="text" class="phone-number" id="telefono_number"
                                   maxlength="7" placeholder="1234567"
                                   autocomplete="off" inputmode="numeric"
                                   value="<?= e($telNum) ?>">
                            <input type="hidden" name="telefono" id="telefono">
                        </div>
                        <span class="field-error" id="telefono-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= e($get('fecha_nac', $get('fecha_nacimiento'))) ?>" max="<?= date('Y-m-d', strtotime('-6 years')) ?>">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Sexo</label>
                        <select name="sexo" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <option value="M" <?= $get('sexo') === 'M' ? 'selected' : '' ?>>Masculino</option>
                            <option value="F" <?= $get('sexo') === 'F' ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Categoría</label>
                        <select name="categoria_id" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int) $c['categoria_id'] ?>" <?= ((int) $get('categoria_id') === (int) $c['categoria_id']) ? 'selected' : '' ?>>
                                    <?= e($c['nombre_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Posición de juego</label>
                        <select name="posicion_de_juego" class="form-control">
                            <option value="">Sin definir</option>
                            <?php foreach ($posiciones as $p): ?>
                                <option value="<?= (int) $p['posicion_id'] ?>" <?= ((int) $get('posicion_juego_id', $get('posicion_de_juego')) === (int) $p['posicion_id']) ? 'selected' : '' ?>>
                                    <?= e($p['nombre_posicion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label">Pierna dominante</label>
                        <select name="pierna_dominante" class="form-control">
                            <option value="">Sin definir</option>
                            <?php foreach (PIERNA_DOMINANTE as $op): ?>
                                <option value="<?= e($op) ?>" <?= $get('pierna_dominante') === $op ? 'selected' : '' ?>><?= e(ucfirst($op)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estatus</label>
                        <select name="estatus" class="form-control">
                            <?php foreach (ESTATUS_ATLETA as $op => $label):
                                $cur = $get('estatus', 1); ?>
                                <option value="<?= (int)$op ?>" <?= (int)$cur === (int)$op ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Foto de Perfil</label>
                        <div class="af-file-upload">
                            <input type="file" name="foto" id="foto-input" class="af-file-input" accept="image/jpeg,image/png,image/webp">
                            <label for="foto-input" class="af-file-label" id="foto-label">
                                <i class="ph ph-camera"></i>
                                <span>Subir foto</span>
                            </label>
                            <div class="af-file-preview" id="foto-preview-container" style="<?= empty($a['foto']) ? 'display:none;' : '' ?>">
                                <img src="<?= !empty($a['foto']) ? e(url($a['foto'])) : '' ?>" id="foto-preview-img" alt="Vista previa">
                                <button type="button" class="af-file-remove" id="btn-remove-foto" title="Quitar foto"><i class="ph ph-x"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
