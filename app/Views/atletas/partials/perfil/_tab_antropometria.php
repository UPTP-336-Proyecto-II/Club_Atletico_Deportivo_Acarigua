            <!-- Tab: AntropometrÃ­a -->
            <div id="tab-antropometria" class="tab-content" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="margin: 0;"><i class="ph ph-ruler"></i> EvoluciÃ³n FÃ­sica</h3>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-nueva-medicion"><i
                            class="ph ph-plus"></i> Nueva MediciÃ³n</button>
                </div>

                <!-- Mock Chart Container -->
                <div style="height: 300px; background: var(--color-bg-alt); border-radius: var(--radius); border: 1px solid var(--color-border); margin-bottom: 24px; position: relative;"
                    id="chart-antropometria">
                    <!-- ECharts renders here -->
                </div>

                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="data-table" id="tabla-antropometria" style="min-width: 900px;">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Peso (kg)</th>
                                <th>Altura (cm)</th>
                                <th>% Grasa</th>
                                <th>% Musc.</th>
                                <th>Env. (cm)</th>
                                <th>Pierna (cm)</th>
                                <th>Torso (cm)</th>
                                <th>IMC</th>
                                <?php if (can('admin')): ?>
                                    <th style="width: 110px; text-align: center;">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="tabla-medidas-body">
                            <?php if (empty($medidas_historial)): ?>
                                <tr>
                                    <td colspan="<?= can('admin') ? 10 : 9 ?>"
                                        style="text-align: center; padding: 32px; color: var(--color-text-muted);">No hay
                                        mediciones registradas.</td>
                                </tr>
                            <?php else:
                                foreach (array_reverse($medidas_historial) as $m): ?>
                                    <tr>
                                        <td><?= e(date('d/m/Y', strtotime($m['fecha_medicion']))) ?></td>
                                        <td><?= e($m['peso'] ?? 'â') ?></td>
                                        <td><?= e($m['altura'] ?? 'â') ?></td>
                                        <td><?= !empty($m['porcentaje_grasa']) ? e($m['porcentaje_grasa']) . '%' : 'â' ?></td>
                                        <td><?= !empty($m['porcentaje_musculatura']) ? e($m['porcentaje_musculatura']) . '%' : 'â' ?></td>
                                        <td><?= e($m['envergadura'] ?? 'â') ?></td>
                                        <td><?= e($m['largo_de_pierna'] ?? 'â') ?></td>
                                        <td><?= e($m['largo_de_torso'] ?? 'â') ?></td>
                                        <td style="white-space: nowrap;">
                                            <?php 
                                            $peso = (float)($m['peso'] ?? 0);
                                            $altura = (float)($m['altura'] ?? 0);
                                            // Estandarizado en cm
                                            $altura = $altura / 100;
                                            if ($peso > 0 && $altura > 0):
                                                $imc = $peso / ($altura * $altura);
                                                $badgeClass = 'success';
                                                $label = 'Normal';
                                                if ($imc < 18.5) {
                                                    $badgeClass = 'warning';
                                                    $label = 'Bajo peso';
                                                } elseif ($imc >= 25 && $imc < 30) {
                                                    $badgeClass = 'warning';
                                                    $label = 'Sobrepeso';
                                                } elseif ($imc >= 30) {
                                                    $badgeClass = 'danger';
                                                    $label = 'Obesidad';
                                                }
                                                ?>
                                                <span class="badge badge-<?= $badgeClass ?>"><?= number_format($imc, 1) ?>
                                                    (<?= $label ?>)</span>
                                            <?php else: ?>
                                                â
                                            <?php endif; ?>
                                        </td>
                                        <?php if (can('admin')): ?>
                                            <td style="text-align: center;">
                                                <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                                    <button type="button" class="btn-icon-premium btn-editar-medicion" 
                                                        data-id="<?= $m['medidas_id'] ?>"
                                                        data-fecha="<?= e($m['fecha_medicion']) ?>"
                                                        data-peso="<?= e($m['peso']) ?>"
                                                        data-altura="<?= e($m['altura']) ?>"
                                                        data-grasa="<?= e($m['porcentaje_grasa']) ?>"
                                                        data-musculo="<?= e($m['porcentaje_musculatura']) ?>"
                                                        data-envergadura="<?= e($m['envergadura']) ?>"
                                                        data-pierna="<?= e($m['largo_de_pierna']) ?>"
                                                        data-torso="<?= e($m['largo_de_torso']) ?>"
                                                        title="Editar mediciÃ³n"
                                                        style="width: 28px; height: 28px; font-size: 14px;">
                                                        <i class="ph ph-pencil-simple"></i>
                                                    </button>
                                                    <button type="button" class="btn-icon-premium btn-eliminar-medicion"
                                                        data-id="<?= $m['medidas_id'] ?>"
                                                        title="Eliminar mediciÃ³n"
                                                        style="width: 28px; height: 28px; font-size: 14px; color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2);">
                                                        <i class="ph ph-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal: Nueva MediciÃ³n -->
            <div id="modal-medicion" class="modal-overlay" style="display:none;">
                <form id="form-medicion" action="<?= e(url("/admin/medidas/atleta/{$atleta['atleta_id']}")) ?>"
                    method="POST" class="modal-container" style="max-width: 600px;">
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-ruler"></i> Nueva MediciÃ³n AntropomÃ©trica</h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div id="medicion-error"
                            style="display:none; background:rgba(239, 68, 68, 0.1); color:var(--color-danger); padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">Fecha de MediciÃ³n *</label>
                                <input type="date" name="fecha_medicion" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" step="0.1" name="peso" class="form-control" placeholder="Ej: 70.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Altura (cm)</label>
                                <input type="number" step="0.1" name="altura" class="form-control"
                                    placeholder="Ej: 175">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">% Grasa</label>
                                <input type="number" step="0.1" name="porcentaje_grasa" class="form-control"
                                    placeholder="Ej: 12.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label">% Musculatura</label>
                                <input type="number" step="0.1" name="porcentaje_musculatura" class="form-control"
                                    placeholder="Ej: 40.2">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Envergadura (cm)</label>
                                <input type="number" step="0.1" name="envergadura" class="form-control"
                                    placeholder="Ej: 180">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pierna (cm)</label>
                                <input type="number" step="0.1" name="largo_de_pierna" class="form-control"
                                    placeholder="Ej: 90">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Torso (cm)</label>
                                <input type="number" step="0.1" name="largo_de_torso" class="form-control"
                                    placeholder="Ej: 50">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar
                            MediciÃ³n</button>
                    </div>
                </form>
            </div>

            <!-- Modal: Editar MediciÃ³n -->
            <div id="modal-medicion-editar" class="modal-overlay" style="display:none;">
                <form id="form-medicion-editar" action="" method="POST" class="modal-container" style="max-width: 600px;" novalidate>
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="ph ph-ruler"></i> Editar MediciÃ³n AntropomÃ©trica</h3>
                        <button type="button" class="modal-close" data-close-modal>&times;</button>
                    </div>
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div id="medicion-editar-error"
                            style="display:none; background:rgba(239, 68, 68, 0.1); color:var(--color-danger); padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">Fecha de MediciÃ³n *</label>
                                <input type="date" name="fecha_medicion" id="edit-fecha_medicion" class="form-control" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" step="0.1" name="peso" id="edit-peso" class="form-control" placeholder="Ej: 70.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Altura (cm)</label>
                                <input type="number" step="0.1" name="altura" id="edit-altura" class="form-control" placeholder="Ej: 175">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label class="form-label">% Grasa</label>
                                <input type="number" step="0.1" name="porcentaje_grasa" id="edit-porcentaje_grasa" class="form-control" placeholder="Ej: 12.5">
                            </div>
                            <div class="form-group">
                                <label class="form-label">% Musculatura</label>
                                <input type="number" step="0.1" name="porcentaje_musculatura" id="edit-porcentaje_musculatura" class="form-control" placeholder="Ej: 40.2">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Envergadura (cm)</label>
                                <input type="number" step="0.1" name="envergadura" id="edit-envergadura" class="form-control" placeholder="Ej: 180">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pierna (cm)</label>
                                <input type="number" step="0.1" name="largo_de_pierna" id="edit-largo_de_pierna" class="form-control" placeholder="Ej: 90">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Torso (cm)</label>
                                <input type="number" step="0.1" name="largo_de_torso" id="edit-largo_de_torso" class="form-control" placeholder="Ej: 50">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
