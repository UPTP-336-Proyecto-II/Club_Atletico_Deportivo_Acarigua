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
                        <input type="text" name="nombre" class="form-control" required maxlength="50" value="<?= e($get('nombre', '')) ?>" placeholder="Ej: Juan Carlos">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Apellidos</label>
                        <input type="text" name="apellido" class="form-control" required maxlength="50" value="<?= e($get('apellido', '')) ?>" placeholder="Ej: Pérez Rodríguez">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label" id="label-cedula">Documento de Identidad</label>
                        <?php
                            $cedVal   = $get('cedula', '');
                            $cedPref  = 'V';
                            $cedNum   = '';
                            if (!empty($cedVal)) {
                                if (str_contains($cedVal, '-')) {
                                    [$cedPref, $cedNum] = explode('-', $cedVal, 2);
                                } else {
                                    $firstChar = strtoupper($cedVal[0]);
                                    if (in_array($firstChar, ['V', 'E', 'P', 'N'])) {
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
                                <option value="N" <?= $cedPref==='N'?'selected':'' ?>>N</option>
                            </select>
                            <span class="phone-sep">-</span>
                            <!-- Input para Cédula o Pasaporte -->
                            <input type="text" class="phone-number" id="cedula_number"
                                   maxlength="10" placeholder="12345678"
                                   autocomplete="off"
                                   value="<?= $cedPref !== 'N' ? e($cedNum) : '' ?>"
                                   <?= $cedPref === 'N' ? 'style="display:none;"' : '' ?>>
                                   
                            <!-- Inputs para Partida -->
                            <div id="folio_inputs" style="display: <?= $cedPref === 'N' ? 'flex' : 'none' ?>; flex: 1; align-items: center;">
                                <?php
                                    $fYear = ''; $fActa = '';
                                    if ($cedPref === 'N') {
                                        $fParts = explode('-', $cedNum);
                                        if (count($fParts) >= 2) {
                                            $fYear = $fParts[0]; $fActa = $fParts[1];
                                        } else {
                                            $fYear = $cedNum;
                                        }
                                    }
                                ?>
                                <input type="text" id="folio_year" class="phone-number" style="width:55px; padding:0 4px; text-align:center;" placeholder="Año" maxlength="4" value="<?= e($fYear) ?>">
                                <span class="phone-sep">-</span>
                                <input type="text" id="folio_acta" class="phone-number" style="min-width:0; flex:1; padding:0 4px; text-align:center;" placeholder="Acta" maxlength="5" value="<?= e($fActa) ?>">
                            </div>
                            
                            <input type="hidden" name="cedula" id="cedula" value="<?= e($cedVal) ?>">
                        </div>
                        <span class="field-error" id="cedula-error"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="label-telefono">Tel&eacute;fono</label>
                        <?php
                            $telVal   = $get('telefono', '');
                            $telPref  = '';
                            $telNum   = '';
                            foreach (['0412','0414','0416','0422','0424','0426','0255','0256'] as $_p) {
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
                                <option value="0255" <?= $telPref==='0255'?'selected':'' ?>>0255</option>
                                <option value="0256" <?= $telPref==='0256'?'selected':'' ?>>0256</option>
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
                        <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= e($get('fecha_nac', $get('fecha_nacimiento', ''))) ?>" max="<?= date('Y-m-d', strtotime('-6 years')) ?>">
                    </div>
                </div>

                <div class="af-grid af-grid--3">
                    <div class="form-group">
                        <label class="form-label"><span class="required">*</span> Sexo</label>
                        <select name="sexo" class="form-control" required>
                            <option value="">— Seleccione —</option>
                            <option value="M" <?= $get('sexo', '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                            <option value="F" <?= $get('sexo', '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pierna dominante</label>
                        <select name="pierna_dominante" class="form-control">
                            <option value="">Sin definir</option>
                            <?php foreach (PIERNA_DOMINANTE as $op): ?>
                                <option value="<?= e($op) ?>" <?= $get('pierna_dominante', '') === $op ? 'selected' : '' ?>><?= e(ucfirst($op)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="af-grid af-grid--2">
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
