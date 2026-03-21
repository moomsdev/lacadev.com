<?php
	/**
	 * App Layout: layouts/app.php
	 *
	 * Template for displaying a single project as a professional quotation document.
	 *
	 * @link    https://codex.wordpress.org/Template_Hierarchy
	 *
	 * @package WPEmergeTheme
	 */

	while (have_posts()) : the_post();

		$postId = get_the_ID();
		$isClientView = !current_user_can('edit_post', $postId);
		$clientViewParam = sanitize_key($_GET['client_view'] ?? '');
		if ($clientViewParam === '1' || $clientViewParam === 'true') {
			$isClientView = true;
		}
		if ($clientViewParam === '0' || $clientViewParam === 'false') {
			$isClientView = false;
		}

		// --- Quotation fields ---
		$quotationIntro    = carbon_get_post_meta($postId, 'quotation_intro');
		$designPages       = carbon_get_post_meta($postId, 'design_pages');
		$backendFeatures   = carbon_get_post_meta($postId, 'backend_features');
		$timelinePhases    = carbon_get_post_meta($postId, 'timeline_phases');
		$quotationItems    = carbon_get_post_meta($postId, 'quotation_items');
		$validDays         = carbon_get_post_meta($postId, 'quotation_valid_days') ?: '15';

		// --- Client Info ---
		$clientName    = carbon_get_post_meta($postId, 'client_name');
		$clientEmail   = carbon_get_post_meta($postId, 'client_email');
		$clientPhone   = carbon_get_post_meta($postId, 'client_phone');
		$clientAddress = carbon_get_post_meta($postId, 'client_address');

		// --- Status & Timeline ---
		$projectStatus = carbon_get_post_meta($postId, 'project_status');
		$estimatedDays = carbon_get_post_meta($postId, 'estimated_days');
		$dateStart     = carbon_get_post_meta($postId, 'date_start');
		$dateHandover  = carbon_get_post_meta($postId, 'date_handover');

		// --- Finance ---
		$priceBuild       = carbon_get_post_meta($postId, 'price_build');
		$priceMaintenance = carbon_get_post_meta($postId, 'price_maintenance_yearly');
		$domainPrice      = carbon_get_post_meta($postId, 'domain_price');
		$hostingPrice     = carbon_get_post_meta($postId, 'hosting_price');
		$paymentHistory   = carbon_get_post_meta($postId, 'payment_history');
		$paymentStatus    = carbon_get_post_meta($postId, 'payment_status');

		// --- Tech ---
		$brandColors    = carbon_get_post_meta($postId, 'brand_colors');
		$demoUrl        = carbon_get_post_meta($postId, 'demo_design_url');

		// --- Site meta ---
		$logoId    = carbon_get_theme_option('logo');
		$logoUrl   = $logoId ? wp_get_attachment_image_url($logoId, 'full') : '';
		$siteEmail = getOption('email') ?: get_bloginfo('admin_email');
		$sitePhone = getOption('phone_number');
		$phoneNumber = str_replace(['.', ' '], '', $sitePhone);
		$siteAddress = getOption('address');

		// --- State maps ---
		$statusLabels = [
			'pending'     => ['label' => __('Chờ duyệt', 'laca'),      'class' => 'badge--warning'],
			'in_progress' => ['label' => __('Đang thực hiện', 'laca'), 'class' => 'badge--info'],
			'done'        => ['label' => __('Hoàn thành', 'laca'),     'class' => 'badge--success'],
			'maintenance' => ['label' => __('Bảo trì', 'laca'),        'class' => 'badge--neutral'],
			'paused'      => ['label' => __('Tạm dừng', 'laca'),       'class' => 'badge--neutral'],
		];
		$statusInfo = $statusLabels[$projectStatus] ?? ['label' => __('Không rõ', 'laca'), 'class' => 'badge--neutral'];

		$paymentLabels = [
			'pending' => ['label' => __('Chưa thanh toán', 'laca'),        'class' => 'badge--warning'],
			'partial' => ['label' => __('Đã thanh toán một phần', 'laca'), 'class' => 'badge--info'],
			'paid'    => ['label' => __('Đã thanh toán đủ', 'laca'),       'class' => 'badge--success'],
			'overdue' => ['label' => __('Quá hạn', 'laca'),                'class' => 'badge--danger'],
		];
		$paymentInfo = $paymentLabels[$paymentStatus] ?? ['label' => '—', 'class' => 'badge--neutral'];



		// --- Finance calc ---
		$totalPaid = 0;
		if (!empty($paymentHistory)) {
			foreach ($paymentHistory as $ph) {
				$totalPaid += (int) preg_replace('/[^0-9]/', '', $ph['pay_amount'] ?? '0');
			}
		}
		$priceBuildNum = (int) preg_replace('/[^0-9]/', '', $priceBuild ?? '0');
		$remaining     = $priceBuildNum - $totalPaid;

		// Quotation items total
		$quotationTotal = 0;
		if (!empty($quotationItems)) {
			foreach ($quotationItems as $qi) {
				$unitPrice = (int) preg_replace('/[^0-9]/', '', $qi['item_unit_price'] ?? '0');
				$qty       = max(1, (int) ($qi['item_qty'] ?? 1));
				$quotationTotal += $unitPrice * $qty;
			}
		}

		// Date issued & validity
		$dateIssued = get_the_date('d/m/Y');
		$validUntil = date('d/m/Y', strtotime('+' . intval($validDays) . ' days', strtotime(get_the_date('Y-m-d'))));

	endwhile;
?>

<article class="quotation-doc">
    <?php get_template_part('template-parts/post-hero'); ?>

	<!-- ============================================================
	     DOCUMENT HEADER
	     ============================================================ -->
	<header class="qd-header">
		<div class="qd-header__brand">
			<?php
				$clientLogoId = get_post_thumbnail_id($postId);
				$clientAlt    = $clientName ?: get_the_title();
			?>

			<?php if ($clientLogoId) : ?>
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<?php
						echo getResponsivePostThumbnail($postId, 'tablet', [
							'class'    => 'qd-header__logo qd-header__logo--client',
							'alt'      => $clientAlt,
							'loading'  => 'eager',
							'decoding' => 'async',
						]);
					?>
				</a>
			<?php elseif ($logoId) : ?>
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<?php
						echo getResponsiveOption('logo', 'tablet', [
							'class'    => 'qd-header__logo',
							'alt'      => get_bloginfo('name'),
							'loading'  => 'eager',
							'decoding' => 'async',
						]);
					?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url(home_url('/')); ?>" class="qd-header__site-name"><?php bloginfo('name'); ?></a>
			<?php endif; ?>
		</div>

		<div class="qd-header__info">
			<h1 class="qd-header__title"><?php echo esc_html__('Báo giá dịch vụ', 'laca'); ?></h1>
			<table class="qd-header__meta-table">
				<tr>
					<td><?php echo esc_html__('Số báo giá', 'laca'); ?></td>
					<td><strong>#<?php echo get_the_ID(); ?></strong></td>
				</tr>
				<tr>
					<td><?php echo esc_html__('Ngày lập', 'laca'); ?></td>
					<td><?php echo esc_html($dateIssued); ?></td>
				</tr>
				<tr>
					<td><?php echo esc_html__('Hiệu lực đến', 'laca'); ?></td>
					<td><?php echo esc_html($validUntil); ?></td>
				</tr>
				<?php if (!$isClientView) : ?>
					<tr>
						<td><?php echo esc_html__('Trạng thái', 'laca'); ?></td>
						<td><span class="badge <?php echo esc_attr($statusInfo['class']); ?>"><?php echo esc_html($statusInfo['label']); ?></span></td>
					</tr>
				<?php endif; ?>
			</table>
		</div>
	</header>

	<!-- Parties: Provider ↔ Client -->
	<div class="qd-parties">
		<div class="qd-party">
			<p class="qd-party__role"><?php echo esc_html__('Bên cung cấp dịch vụ (Bên A)', 'laca'); ?></p>
			<p class="qd-party__name"><?php bloginfo('name'); ?></p>
			<?php if ($siteEmail) : ?>
				<p class="qd-party__detail">
					<strong><?php echo esc_html__('Email:', 'laca'); ?></strong> <a href="mailto:<?php echo esc_attr($siteEmail); ?>"><?php echo esc_html($siteEmail); ?></a>
				</p>
			<?php endif; ?>
			<?php if ($sitePhone) : ?>
				<p class="qd-party__detail"><strong><?php echo esc_html__('ĐT:', 'laca'); ?></strong> <a href="tel:<?php echo esc_attr($phoneNumber); ?>"><?php echo esc_html($sitePhone); ?></a></p>
			<?php endif; ?>
			<?php if ($siteAddress) : ?>
				<p class="qd-party__detail"><strong><?php echo esc_html__('Địa chỉ:', 'laca'); ?></strong> <?php echo esc_html($siteAddress); ?></p>
			<?php endif; ?>
		</div>

		<div class="qd-party qd-party--client">
			<p class="qd-party__role"><?php echo esc_html__('Bên sử dụng dịch vụ (Bên B)', 'laca'); ?></p>
			<p class="qd-party__name"><?php echo esc_html($clientName ?: get_the_title()); ?></p>
			<?php if ($clientEmail) : ?>
				<p class="qd-party__detail"><strong><?php echo esc_html__('Email:', 'laca'); ?></strong> <a href="mailto:<?php echo esc_attr($clientEmail); ?>"><?php echo esc_html($clientEmail); ?></a></p>
			<?php endif; ?>
			<?php if ($clientPhone) : ?>
				<p class="qd-party__detail"><strong><?php echo esc_html__('ĐT:', 'laca'); ?></strong> <a href="tel:<?php echo esc_attr(preg_replace('/\s/', '', $clientPhone)); ?>"><?php echo esc_html($clientPhone); ?></a></p>
			<?php endif; ?>
			<?php if ($clientAddress) : ?>
				<p class="qd-party__detail"><strong><?php echo esc_html__('Địa chỉ:', 'laca'); ?></strong> <?php echo esc_html($clientAddress); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php
		$designPagesCount = !empty($designPages) && is_array($designPages) ? count($designPages) : 0;
		$totalCostLabel   = $priceBuild ?: ($quotationTotal > 0 ? number_format($quotationTotal, 0, ',', '.') : '');
	?>

	<?php if ($totalCostLabel || $estimatedDays || $designPagesCount || $dateHandover) : ?>
		<div class="qd-metrics" aria-label="<?php echo esc_attr__('Tóm tắt nhanh', 'laca'); ?>">
			<?php if ($totalCostLabel) : ?>
				<div class="qd-metric">
					<div class="qd-metric__label"><?php echo esc_html__('Tổng chi phí', 'laca'); ?></div>
					<div class="qd-metric__value"><?php echo esc_html($totalCostLabel); ?> <?php echo esc_html__('đ', 'laca'); ?></div>
				</div>
			<?php endif; ?>

			<?php if ($estimatedDays) : ?>
				<div class="qd-metric">
					<div class="qd-metric__label"><?php echo esc_html__('Thời gian dự kiến', 'laca'); ?></div>
					<div class="qd-metric__value"><?php echo esc_html($estimatedDays); ?> <?php echo esc_html__('ngày', 'laca'); ?></div>
				</div>
			<?php endif; ?>

			<?php if ($designPagesCount) : ?>
				<div class="qd-metric">
					<div class="qd-metric__label"><?php echo esc_html__('Số lượng trang', 'laca'); ?></div>
					<div class="qd-metric__value"><?php echo esc_html((string) $designPagesCount); ?></div>
				</div>
			<?php endif; ?>

			<?php if ($dateHandover) : ?>
				<div class="qd-metric">
					<div class="qd-metric__label"><?php echo esc_html__('Bàn giao dự kiến', 'laca'); ?></div>
					<div class="qd-metric__value"><?php echo esc_html(date('d/m/Y', strtotime($dateHandover))); ?></div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<hr class="qd-rule">

	<!-- ============================================================
	     SECTION I — GIỚI THIỆU
	     ============================================================ -->
	<?php if ($quotationIntro || get_the_content()) : ?>
		<section class="qd-section">
			<h2 class="qd-section__heading"><span class="qd-section__num">I</span> <?php echo esc_html__('Giới thiệu', 'laca'); ?></h2>
			<div class="qd-prose">
				<?php
				if ($quotationIntro) {
					echo wp_kses_post(apply_filters('the_content', $quotationIntro));
				} else {
					theContent();
				}
				?>
			</div>
			<?php if ($demoUrl) : ?>
				<a href="<?php echo esc_url($demoUrl); ?>" target="_blank" rel="noopener noreferrer" class="qd-demo-link">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
					<?php echo esc_html__('Xem thiết kế mẫu / Figma', 'laca'); ?>
				</a>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- ============================================================
	     SECTION II — PHẠM VI CÔNG VIỆC
	     ============================================================ -->
	<?php if (!empty($designPages) || $backendFeatures) : ?>
		<section class="qd-section">
			<h2 class="qd-section__heading"><span class="qd-section__num">II</span> <?php echo esc_html__('Phạm vi công việc', 'laca'); ?></h2>

			<!-- 2A: Trang thiết kế -->
			<?php if (!empty($designPages)) : ?>
				<h3 class="qd-subsection"><?php echo esc_html__('A. Danh sách trang thiết kế', 'laca'); ?></h3>
				<table class="qd-table">
					<thead>
						<tr>
							<th class="qd-table__col-num"><?php echo esc_html__('STT', 'laca'); ?></th>
							<th><?php echo esc_html__('Tên trang', 'laca'); ?></th>
							<th><?php echo esc_html__('Website mẫu tham khảo', 'laca'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($designPages as $i => $page) : ?>
							<tr>
								<td class="qd-table__col-num"><?php echo ($i + 1); ?></td>
								<td><?php echo esc_html($page['page_name'] ?? ''); ?></td>
								<td>
									<?php if (!empty($page['page_demo_url'])) : ?>
										<a href="<?php echo esc_url($page['page_demo_url']); ?>" target="_blank" rel="noopener noreferrer" class="qd-link">
											<?php echo esc_html(parse_url($page['page_demo_url'], PHP_URL_HOST) ?: $page['page_demo_url']); ?>
										</a>
									<?php else : ?>
										—
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<!-- 2B: Màu sắc chủ đạo -->
			<?php if (!empty($brandColors) && is_array($brandColors)) : ?>
				<h3 class="qd-subsection"><?php echo esc_html__('B. Bảng màu chủ đạo', 'laca'); ?></h3>
				<div class="qd-color-palette" aria-label="<?php echo esc_attr__('Bảng màu', 'laca'); ?>">
					<?php foreach ($brandColors as $c) :
						$hex = isset($c['hex']) ? (string) $c['hex'] : '';
						$label = isset($c['label']) ? (string) $c['label'] : '';
						if (!$hex) continue;
						if ($hex[0] !== '#') {
							$hex = '#' . $hex;
						}
						$hex = strtoupper($hex);
					?>
						<div class="qd-color-chip">
							<span class="qd-color-chip__swatch" style="background: <?php echo esc_attr($hex); ?>;" aria-hidden="true"></span>
							<span class="qd-color-chip__code"><?php echo esc_html($hex); ?></span>
							<?php if ($label) : ?>
								<span class="qd-color-chip__label"><?php echo esc_html($label); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- 2C: Lập trình backend -->
			<?php if ($backendFeatures) : ?>
				<h3 class="qd-subsection"><?php echo esc_html__('C. Tính năng kỹ thuật / Lập trình Backend', 'laca'); ?></h3>
				<div class="qd-prose">
					<?php echo wp_kses_post(apply_filters('the_content', $backendFeatures)); ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- ============================================================
	     SECTION III — THỜI GIAN THỰC HIỆN
	     ============================================================ -->
	<?php if ($estimatedDays || !empty($timelinePhases) || $dateStart || $dateHandover) : ?>
		<section class="qd-section">
			<h2 class="qd-section__heading"><span class="qd-section__num">III</span> <?php echo esc_html__('Thời gian thực hiện', 'laca'); ?></h2>

			<?php if ($estimatedDays || $dateStart || $dateHandover) : ?>
				<div class="qd-timeline-summary">
					<?php if ($estimatedDays) : ?>
						<div class="qd-timeline-chip">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
							<?php echo esc_html__('Tổng thời gian:', 'laca'); ?> <strong><?php echo esc_html($estimatedDays); ?> <?php echo esc_html__('ngày', 'laca'); ?></strong>
						</div>
					<?php endif; ?>
					<?php if ($dateStart) : ?>
						<div class="qd-timeline-chip">
							<?php echo esc_html__('Dự kiến bắt đầu:', 'laca'); ?> <strong><?php echo esc_html(date('d/m/Y', strtotime($dateStart))); ?></strong>
						</div>
					<?php endif; ?>
					<?php if ($dateHandover) : ?>
						<div class="qd-timeline-chip">
							<?php echo esc_html__('Dự kiến bàn giao:', 'laca'); ?> <strong><?php echo esc_html(date('d/m/Y', strtotime($dateHandover))); ?></strong>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($timelinePhases)) : ?>
				<ol class="qd-flow" aria-label="<?php echo esc_attr__('Các giai đoạn thực hiện', 'laca'); ?>">
					<?php foreach ($timelinePhases as $index => $phase) : ?>
						<li class="qd-flow__item">
							<div class="qd-flow__node" aria-hidden="true"></div>
							<div class="qd-flow__card">
								<div class="qd-flow__head">
									<div class="qd-flow__title">
										<span class="qd-flow__index"><?php echo esc_html((string) ($index + 1)); ?></span>
										<span><?php echo esc_html($phase['phase_name'] ?? ''); ?></span>
									</div>
									<div class="qd-flow__meta">
										<?php if (!empty($phase['phase_days'])) : ?>
											<?php echo esc_html($phase['phase_days']); ?> <?php echo esc_html__('ngày', 'laca'); ?>
										<?php else : ?>
											<?php echo esc_html__('—', 'laca'); ?>
										<?php endif; ?>
									</div>
								</div>
								<div class="qd-prose qd-prose--sm qd-flow__body">
									<?php echo wp_kses_post(apply_filters('the_content', $phase['phase_content'] ?? '')); ?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- ============================================================
	     SECTION IV — CHI PHÍ THỰC HIỆN
	     ============================================================ -->
	<?php if (!empty($quotationItems) || $priceBuild) : ?>
		<section class="qd-section">
			<h2 class="qd-section__heading"><span class="qd-section__num">IV</span> <?php echo esc_html__('Chi phí thực hiện', 'laca'); ?></h2>

			<?php if (!empty($quotationItems)) : ?>
				<table class="qd-table qd-table--cost">
					<thead>
						<tr>
							<th class="qd-table__col-num"><?php echo esc_html__('STT', 'laca'); ?></th>
							<th><?php echo esc_html__('Mô tả hạng mục', 'laca'); ?></th>
							<th class="qd-table__col-price"><?php echo esc_html__('Đơn giá', 'laca'); ?></th>
							<th class="qd-table__col-qty"><?php echo esc_html__('SL', 'laca'); ?></th>
							<th class="qd-table__col-price"><?php echo esc_html__('Thành tiền / Ghi chú', 'laca'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($quotationItems as $i => $qi) :
							$unitPrice = (int) preg_replace('/[^0-9]/', '', $qi['item_unit_price'] ?? '0');
							$qty       = max(1, (int) ($qi['item_qty'] ?? 1));
							$lineTotal = $unitPrice * $qty;
						?>
							<tr>
								<td class="qd-table__col-num" data-label="<?php echo esc_attr__('STT', 'laca'); ?>"><?php echo ($i + 1); ?></td>
								<td data-label="<?php echo esc_attr__('Mô tả hạng mục', 'laca'); ?>"><?php echo esc_html($qi['item_name'] ?? ''); ?></td>
								<td class="qd-table__col-price qd-amount" data-label="<?php echo esc_attr__('Đơn giá', 'laca'); ?>">
									<?php echo !empty($qi['item_unit_price']) ? esc_html($qi['item_unit_price']) : '—'; ?>
								</td>
								<td class="qd-table__col-qty" data-label="<?php echo esc_attr__('SL', 'laca'); ?>"><?php echo esc_html($qi['item_qty'] ?? '1'); ?></td>
								<td class="qd-table__col-price qd-amount" data-label="<?php echo esc_attr__('Thành tiền / Ghi chú', 'laca'); ?>">
									<?php
									if (!empty($qi['item_note'])) {
										echo esc_html($qi['item_note']);
									} elseif ($lineTotal > 0) {
										echo number_format($lineTotal, 0, ',', '.') . ' đ';
									} else {
										echo '—';
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<?php if ($quotationTotal > 0 || $priceBuild) : ?>
						<tfoot>
							<tr class="qd-table__total-row">
								<td colspan="4"><strong><?php echo esc_html__('Tổng chi phí xây dựng', 'laca'); ?></strong></td>
								<td class="qd-table__col-price qd-amount qd-amount--total">
									<?php
									if ($priceBuild) {
										echo esc_html($priceBuild) . ' ' . esc_html__('đ', 'laca');
									} else {
										echo esc_html(number_format($quotationTotal, 0, ',', '.')) . ' ' . esc_html__('đ', 'laca');
									}
									?>
								</td>
							</tr>

							<?php if ($priceMaintenance) : ?>
								<tr class="qd-table__note-row">
									<td colspan="4"><?php echo esc_html__('Phí bảo trì hàng năm (sau bàn giao)', 'laca'); ?></td>
									<td class="qd-table__col-price qd-amount"><?php echo esc_html($priceMaintenance); ?> <?php echo esc_html__('đ/năm', 'laca'); ?></td>
								</tr>
							<?php endif; ?>
							<?php if ($domainPrice) : ?>
								<tr class="qd-table__note-row">
									<td colspan="4"><?php echo esc_html__('Gia hạn domain / năm', 'laca'); ?></td>
									<td class="qd-table__col-price qd-amount"><?php echo esc_html($domainPrice); ?> <?php echo esc_html__('đ/năm', 'laca'); ?></td>
								</tr>
							<?php endif; ?>
							<?php if ($hostingPrice) : ?>
								<tr class="qd-table__note-row">
									<td colspan="4"><?php echo esc_html__('Gia hạn hosting / năm', 'laca'); ?></td>
									<td class="qd-table__col-price qd-amount"><?php echo esc_html($hostingPrice); ?> <?php echo esc_html__('đ/năm', 'laca'); ?></td>
								</tr>
							<?php endif; ?>
						</tfoot>
					<?php endif; ?>
				</table>
			<?php elseif ($priceBuild) : ?>
				<!-- Chỉ hiển thị tổng nếu không có items chi tiết -->
				<div class="qd-price-summary">
					<span class="qd-price-summary__label"><?php echo esc_html__('Chi phí xây dựng website', 'laca'); ?></span>
					<span class="qd-price-summary__value"><?php echo esc_html($priceBuild); ?> <?php echo esc_html__('đ', 'laca'); ?></span>
				</div>
				<?php if ($priceMaintenance) : ?>
					<div class="qd-price-summary qd-price-summary--muted">
						<span class="qd-price-summary__label"><?php echo esc_html__('Phí bảo trì hàng năm', 'laca'); ?></span>
						<span class="qd-price-summary__value"><?php echo esc_html($priceMaintenance); ?> <?php echo esc_html__('đ/năm', 'laca'); ?></span>
					</div>
				<?php endif; ?>
				<?php if ($domainPrice) : ?>
					<div class="qd-price-summary qd-price-summary--muted">
						<span class="qd-price-summary__label"><?php echo esc_html__('Gia hạn domain / năm', 'laca'); ?></span>
						<span class="qd-price-summary__value"><?php echo esc_html($domainPrice); ?> <?php echo esc_html__('đ/năm', 'laca'); ?></span>
					</div>
				<?php endif; ?>
				<?php if ($hostingPrice) : ?>
					<div class="qd-price-summary qd-price-summary--muted">
						<span class="qd-price-summary__label"><?php echo esc_html__('Gia hạn hosting / năm', 'laca'); ?></span>
						<span class="qd-price-summary__value"><?php echo esc_html($hostingPrice); ?> <?php echo esc_html__('đ/năm', 'laca'); ?></span>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<!-- Lịch sử thanh toán -->
			<?php if (!$isClientView && !empty($paymentHistory)) : ?>
				<h3 class="qd-subsection"><?php echo esc_html__('Lịch sử thanh toán', 'laca'); ?></h3>
				<table class="qd-table qd-table--payment">
					<thead>
						<tr>
							<th><?php echo esc_html__('Ngày', 'laca'); ?></th>
							<th><?php echo esc_html__('Ghi chú', 'laca'); ?></th>
							<th class="qd-table__col-price"><?php echo esc_html__('Số tiền', 'laca'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($paymentHistory as $ph) :
							if (empty($ph['pay_amount'])) continue;
						?>
							<tr>
								<td><?php echo !empty($ph['pay_date']) ? date('d/m/Y', strtotime($ph['pay_date'])) : '—'; ?></td>
								<td><?php echo esc_html($ph['pay_note'] ?? ''); ?></td>
								<td class="qd-table__col-price qd-amount qd-amount--paid"><?php echo esc_html($ph['pay_amount']); ?> <?php echo esc_html__('đ', 'laca'); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<?php if ($priceBuildNum > 0 && $totalPaid > 0) : ?>
						<tfoot>
							<tr>
								<td colspan="2"><strong><?php echo esc_html__('Đã thanh toán', 'laca'); ?></strong></td>
								<td class="qd-table__col-price qd-amount qd-amount--paid"><?php echo esc_html(number_format($totalPaid, 0, ',', '.')); ?> <?php echo esc_html__('đ', 'laca'); ?></td>
							</tr>
							<?php if ($remaining > 0) : ?>
								<tr class="qd-table__remaining-row">
									<td colspan="2"><?php echo esc_html__('Còn lại', 'laca'); ?></td>
									<td class="qd-table__col-price qd-amount qd-amount--danger"><?php echo esc_html(number_format($remaining, 0, ',', '.')); ?> <?php echo esc_html__('đ', 'laca'); ?></td>
								</tr>
							<?php endif; ?>
						</tfoot>
					<?php endif; ?>
				</table>
				<div class="qd-payment-status">
					<?php echo esc_html__('Trạng thái thanh toán:', 'laca'); ?> <span class="badge <?php echo esc_attr($paymentInfo['class']); ?>"><?php echo esc_html($paymentInfo['label']); ?></span>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- ============================================================
	     SECTION V — QUY TRÌNH LÀM VIỆC
	     ============================================================ -->


	<section class="qd-section qd-section--cta">
		<h2 class="qd-section__heading"><span class="qd-section__num">IX</span> <?php echo esc_html__('Liên hệ & xác nhận', 'laca'); ?></h2>

		<div class="qd-cta">
			<div class="qd-cta__copy">
				<p class="qd-cta__title"><?php echo esc_html__('Bạn cần tư vấn nhanh hoặc muốn xác nhận báo giá?', 'laca'); ?></p>
				<p class="qd-cta__desc"><?php echo esc_html__('Hãy liên hệ theo các kênh bên dưới. Chúng tôi phản hồi sớm nhất có thể.', 'laca'); ?></p>

				<div class="qd-cta__meta">
					<?php if ($siteEmail) : ?>
						<div class="qd-cta__meta-item">
							<span class="qd-cta__meta-label"><?php echo esc_html__('Email', 'laca'); ?></span>
							<a class="qd-cta__meta-value" href="mailto:<?php echo esc_attr($siteEmail); ?>"><?php echo esc_html($siteEmail); ?></a>
						</div>
					<?php endif; ?>
					<?php if ($sitePhone) : ?>
						<div class="qd-cta__meta-item">
							<span class="qd-cta__meta-label"><?php echo esc_html__('Điện thoại', 'laca'); ?></span>
							<a class="qd-cta__meta-value" href="tel:<?php echo esc_attr(preg_replace('/\s/', '', $sitePhone)); ?>"><?php echo esc_html($sitePhone); ?></a>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="qd-cta__actions">
				<?php if ($demoUrl) : ?>
					<a class="qd-btn qd-btn--ghost" href="<?php echo esc_url($demoUrl); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html__('Xem thiết kế', 'laca'); ?>
					</a>
				<?php endif; ?>

				<button type="button" class="qd-btn qd-btn--primary" onclick="window.print();">
					<?php echo esc_html__('Tải PDF (In/Lưu)', 'laca'); ?>
				</button>
			</div>
		</div>
	</section>

</article>
