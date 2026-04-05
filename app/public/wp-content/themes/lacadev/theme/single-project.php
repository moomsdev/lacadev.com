<?php
/**
 * App Layout: layouts/app.php
 *
 * Template for displaying a single project as a professional quotation document.
 * Layout theo mẫu Stitch (code.html) với Tailwind CSS.
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 * @package WPEmergeTheme
 */

while (have_posts()):
	the_post();

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
	$quotationIntro = carbon_get_post_meta($postId, 'quotation_intro');
	$designPages = carbon_get_post_meta($postId, 'design_pages');
	$techDescription = carbon_get_post_meta($postId, 'tech_description');
	$techTags = carbon_get_post_meta($postId, 'tech_tags');
	$techModules = carbon_get_post_meta($postId, 'tech_modules');
	$timelinePhases = carbon_get_post_meta($postId, 'timeline_phases');
	$quotationItems = carbon_get_post_meta($postId, 'quotation_items');
	$validDays = carbon_get_post_meta($postId, 'quotation_valid_days') ?: '15';
	$processSteps = carbon_get_post_meta($postId, 'process_steps');
	$paymentSteps = carbon_get_post_meta($postId, 'payment_steps');
	$warrantyStatus = carbon_get_post_meta($postId, 'warranty_status') ?: 'free';
	$warrantyPolicy = carbon_get_post_meta($postId, 'warranty_policy');

	// --- Client Info ---
	$clientName = carbon_get_post_meta($postId, 'client_name');
	$clientEmail = carbon_get_post_meta($postId, 'client_email');
	$clientPhone = carbon_get_post_meta($postId, 'client_phone');
	$clientAddress = carbon_get_post_meta($postId, 'client_address');

	// --- Status & Timeline ---
	$projectStatus = carbon_get_post_meta($postId, 'project_status');
	$estimatedDays = carbon_get_post_meta($postId, 'estimated_days');
	$dateStart = carbon_get_post_meta($postId, 'date_start');
	$dateHandover = carbon_get_post_meta($postId, 'date_handover');

	// --- Finance ---
	$priceBuild = carbon_get_post_meta($postId, 'price_build');
	$priceMaintenance = carbon_get_post_meta($postId, 'price_maintenance_yearly');
	$domainPrice = carbon_get_post_meta($postId, 'domain_price');
	$hostingPrice = carbon_get_post_meta($postId, 'hosting_price');
	$paymentHistory = carbon_get_post_meta($postId, 'payment_history');
	$paymentStatus = carbon_get_post_meta($postId, 'payment_status');

	// --- Tech ---
	$brandColors = carbon_get_post_meta($postId, 'brand_colors');
	$demoUrl = carbon_get_post_meta($postId, 'demo_design_url');

	// --- Site meta ---
	$logoId = carbon_get_theme_option('logo');
	$logoUrl = $logoId ? wp_get_attachment_image_url($logoId, 'full') : '';
	$siteEmail = getOption('email') ?: get_bloginfo('admin_email');
	$sitePhone = getOption('phone_number');
	$phoneNumber = str_replace(['.', ' '], '', $sitePhone);
	$siteAddress = getOption('address');

	// --- Finance calc ---
	$totalPaid = 0;
	if (!empty($paymentHistory)) {
		foreach ($paymentHistory as $ph) {
			$totalPaid += (int) preg_replace('/[^0-9]/', '', $ph['pay_amount'] ?? '0');
		}
	}
	$priceBuildNum = (int) preg_replace('/[^0-9]/', '', $priceBuild ?? '0');
	$remaining = $priceBuildNum - $totalPaid;

	// Quotation items total
	$quotationTotal = 0;
	if (!empty($quotationItems)) {
		foreach ($quotationItems as $qi) {
			$unitPrice = (int) preg_replace('/[^0-9]/', '', $qi['item_unit_price'] ?? '0');
			$qty = max(1, (int) ($qi['item_qty'] ?? 1));
			$quotationTotal += $unitPrice * $qty;
		}
	}

	// Effective total for display
	$totalDisplay = $priceBuild ?: ($quotationTotal > 0 ? number_format($quotationTotal, 0, ',', '.') : '');

	// Date issued & validity
	$dateIssued = get_the_date('F j, Y');
	$validUntil = date('F j, Y', strtotime('+' . intval($validDays) . ' days', strtotime(get_the_date('Y-m-d'))));

	// Ref number
	$refNumber = '#' . get_the_ID() . ' / ' . get_the_date('Y');

	// Warranty labels
	$warrantyLabels = [
		'free' => 'Bảo hành miễn phí',
		'paid' => 'Bảo hành có phí',
		'expired' => 'Hết bảo hành',
		'none' => 'Không áp dụng',
	];
	$warrantyLabel = $warrantyLabels[$warrantyStatus] ?? 'Bảo hành miễn phí';

	// Featured image for hero
	$heroImgUrl = get_the_post_thumbnail_url($postId, 'large') ?: '';

endwhile;
?>



<div class="pt-16 pb-24 max-w-7xl mx-auto px-5 md:px-8">

	<!-- ============================================================
			 HEADER SECTION
			 ============================================================ -->
	<section class="mb-10 md:mb-20">
		<span class="font-label text-xs uppercase tracking-[0.2em] text-[var(--color-secondary)] mb-4 block"><?php _e('Báo giá dự án', 'laca') ?></span>
		<h1 class=" text-4xl md:text-6xl font-extrabold tracking-tighter text-[var(--color-on-surface)] max-w-3xl leading-none">
			<?php echo esc_html(get_the_title()); ?>
		</h1>

		<div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
			<div>
				<p class="font-label uppercase tracking-widest text-[var(--color-outline)] mb-2 text-[10px]"><?php _e('Nhà cung cấp', 'laca') ?></p>
				<p class="font-medium"><?php echo esc_html(get_bloginfo('name')); ?></p>
				<?php if ($siteEmail): ?>
					<p class="text-[var(--color-on-surface-variant)]"><?php echo esc_html($siteEmail); ?></p>
				<?php endif; ?>
				<?php if ($sitePhone): ?>
					<p class="text-[var(--color-on-surface-variant)]"><?php echo esc_html($sitePhone); ?></p>
				<?php endif; ?>
				<?php if ($siteAddress): ?>
					<p class="text-[var(--color-on-surface-variant)]"><?php echo esc_html($siteAddress); ?></p>
				<?php endif; ?>
			</div>
			<div>
				<p class="font-label uppercase tracking-widest text-[var(--color-outline)] mb-2 text-[10px]"><?php _e('Thông tin khách hàng', 'laca') ?></p>
				<p class="font-medium"><?php echo esc_html($clientName ?: get_the_title()); ?></p>
				<?php if ($clientEmail): ?>
					<p class="text-[var(--color-on-surface-variant)]"><?php echo esc_html($clientEmail); ?></p>
				<?php endif; ?>
				<?php if ($clientPhone): ?>
					<p class="text-[var(--color-on-surface-variant)]"><?php echo esc_html($clientPhone); ?></p>
				<?php endif; ?>
				<?php if ($clientAddress): ?>
					<p class="text-[var(--color-on-surface-variant)]"><?php echo esc_html($clientAddress); ?></p>
				<?php endif; ?>
			</div>
			<div>
				<p class="font-label uppercase tracking-widest text-[var(--color-outline)] mb-2 text-[10px]"><?php _e('Mã báo giá', 'laca') ?></p>
				<p class="font-medium"><?php echo esc_html($refNumber); ?></p>
			</div>
			<div>
				<p class="font-label uppercase tracking-widest text-[var(--color-outline)] mb-2 text-[10px]"><?php _e('Ngày phát hành', 'laca') ?></p>
				<p class="font-medium"><?php echo esc_html($dateIssued); ?></p>
			</div>
		</div>
	</section>

	<!-- ============================================================
			 PROJECT SUMMARY SECTION (Hero Image + Summary Card)
			 ============================================================ -->
	<?php if ($heroImgUrl || $quotationIntro): ?>
		<section class="grid grid-cols-1 md:grid-cols-12 gap-0 mb-10 md:mb-20">
			<?php if ($heroImgUrl): ?>
				<div class="md:col-span-9 h-auto md:h-[500px] overflow-hidden rounded-2xl bg-stone-200">
					<img
						alt="<?php echo esc_attr($clientName ?: get_the_title()); ?>"
						class="w-full h-full object-contain md:object-cover"
						src="<?php echo esc_url($heroImgUrl); ?>"
						loading="eager" />
				</div>
			<?php endif; ?>

<div class="<?php echo $heroImgUrl ? 'md:col-span-6 md:col-start-7 md:-mt-16' : 'md:col-span-8'; ?> bg-white p-5 md:p-12 rounded-2xl border border-stone-100">
				<h3 class=" text-2xl font-bold mb-6 tracking-tight"><?php _e('Tổng quan dự án', 'laca') ?></h3>
				<?php if ($quotationIntro): ?>
					<div class="text-[var(--color-on-surface-variant)] leading-relaxed mb-6 prose prose-sm max-w-none">
						<?php echo wp_kses_post(apply_filters('the_content', $quotationIntro)); ?>
					</div>
				<?php endif; ?>

				<?php if ($estimatedDays): ?>
					<div class="flex items-center gap-4 py-4 border-t border-stone-100">
						<span class="material-symbols-outlined text-[var(--color-secondary)]" style="font-variation-settings:'FILL' 1;">timer</span>
						<div>
							<p class="text-xs font-label uppercase text-[var(--color-outline)] tracking-wider"><?php _e('Thời gian thực hiện', 'laca') ?></p>
							<p class="font-semibold text-stone-900"><?php echo esc_html($estimatedDays); ?> 		<?php _e('ngày làm việc', 'laca') ?></p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($demoUrl): ?>
					<div class="flex items-center gap-4 py-4 border-t border-stone-100">
						<span class="material-symbols-outlined text-[var(--color-secondary)]">open_in_new</span>
						<div>
							<p class="text-xs font-label uppercase text-[var(--color-outline)] tracking-wider"><?php _e('Tham khảo thiết kế', 'laca') ?></p>
							<a href="<?php echo esc_url($demoUrl); ?>" target="_blank" rel="noopener noreferrer" class="font-semibold text-stone-900 hover:underline">
								<?php echo esc_html(parse_url($demoUrl, PHP_URL_HOST) ?: $demoUrl); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================================================
			 SCOPE: Danh sách trang thiết kế
			 ============================================================ -->
	<?php if (!empty($designPages)): ?>
		<section class="mb-10 md:mb-20">
			<h2 class="text-2xl md:text-3xl font-bold mb-10 tracking-tight"><?php _e('Phạm vi công việc', 'laca') ?></h2>
			<div class="bg-[var(--color-surface-container-low)] rounded-xl overflow-hidden border border-stone-200/50">
				<div class="px-8 py-6 bg-[var(--color-surface-container)]">
					<h3 class=" font-bold text-lg"><?php _e('Danh sách các trang thiết kế', 'laca') ?></h3>
				</div>
				<div class="p-5 md:p-8">
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-12">
						<?php foreach ($designPages as $i => $page):
							$pageName = $page['page_name'] ?? '';
							$subName = $page['subpage_name'] ?? '';
							if (!$pageName)
								continue;
							?>
							<div class="flex items-center justify-between py-2 border-b border-stone-200">
								<span class="font-medium"><?php echo esc_html(($i + 1) . '. ' . $pageName); ?></span>
								<?php if ($subName): ?>
									<span class="text-xs text-[var(--color-outline)]"><?php echo esc_html($subName); ?></span>
								<?php elseif (!empty($page['page_demo_url'])): ?>
									<a href="<?php echo esc_url($page['page_demo_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-[var(--color-outline)] hover:underline">
										<?php echo esc_html(parse_url($page['page_demo_url'], PHP_URL_HOST) ?: 'Demo'); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================================================
			 TECHNICAL FEATURES & PLATFORM
			 ============================================================ -->
	<?php if (!empty($techModules) || $techDescription): ?>
		<section class="mb-10 md:mb-20 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
			<!-- Left col -->
			<div class="lg:col-span-4">
				<h2 class="text-2xl md:text-3xl font-bold mb-6 tracking-tight leading-tight"><?php _e('Tính năng kỹ thuật & Nền tảng', 'laca') ?></h2>
				<?php if ($techDescription): ?>
					<p class="text-[var(--color-on-surface-variant)] text-sm leading-relaxed mb-6">
						<?php echo esc_html($techDescription); ?>
					</p>
				<?php endif; ?>
				<?php if (!empty($techTags)): ?>
					<div class="flex flex-wrap gap-2">
						<?php foreach ($techTags as $tag): ?>
							<?php if (!empty($tag['tag_name'])): ?>
								<span class="px-4 py-1.5 bg-white border border-stone-200 text-stone-600 text-xs font-semibold rounded-full">
									<?php echo esc_html($tag['tag_name']); ?>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right col: module cards -->
			<?php if (!empty($techModules)):
				// Map màu => Tailwind classes
				$colorMap = [
					'secondary' => ['bg' => 'bg-[var(--color-secondary-container)]', 'text' => 'text-[var(--color-on-secondary-container)]', 'icon' => 'text-[var(--color-secondary)]'],
					'tertiary' => ['bg' => 'bg-[var(--color-tertiary-container)]', 'text' => 'text-[var(--color-on-tertiary-container)]', 'icon' => 'text-stone-600'],
					'primary' => ['bg' => 'bg-[var(--color-primary-container)]', 'text' => 'text-[var(--color-on-primary-container)]', 'icon' => 'text-[var(--color-primary)]'],
					'neutral' => ['bg' => 'bg-stone-200', 'text' => 'text-stone-700', 'icon' => 'text-stone-600'],
				];
				?>
				<div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
					<?php foreach ($techModules as $mod):
						$icon = $mod['module_icon'] ?? 'star';
						$color = $mod['module_color'] ?? 'secondary';
						$title = $mod['module_title'] ?? '';
						$rawItems = $mod['module_items'] ?? '';
						$items = array_filter(array_map('trim', explode("\n", $rawItems)));
						$c = $colorMap[$color] ?? $colorMap['neutral'];
						?>
						<div class="p-5 md:p-8 bg-white rounded-xl border border-stone-200/50 shadow-sm">
							<div class="w-10 h-10 rounded-lg <?php echo esc_attr($c['bg']); ?> flex items-center justify-center mb-6 <?php echo esc_attr($c['text']); ?>">
								<span class="material-symbols-outlined"><?php echo esc_html($icon); ?></span>
							</div>
							<h4 class="font-bold mb-3"><?php echo esc_html($title); ?></h4>
							<?php if (!empty($items)): ?>
								<ul class="text-sm text-[var(--color-on-surface-variant)] space-y-2">
									<?php foreach ($items as $item): ?>
										<li class="flex items-start gap-2">
											<span class="material-symbols-outlined text-[14px] mt-1 <?php echo esc_attr($c['icon']); ?>">check_circle</span>
											<?php echo esc_html($item); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- ============================================================
			 MAINTENANCE & WARRANTY
			 ============================================================ -->
	<?php if ($warrantyPolicy): ?>
		<section class="mb-10 md:mb-20">
			<div class="bg-[var(--color-secondary-container)]/20 p-5 md:p-12 rounded-2xl border border-[var(--color-secondary-container)]">
				<div class="flex flex-col md:flex-row gap-12 items-center">
					<div class="md:w-2/3">
						<div class="prose max-w-none text-[var(--color-on-surface-variant)]">
							<?php echo wp_kses_post(apply_filters('the_content', $warrantyPolicy)); ?>
						</div>
					</div>
					<div class="md:w-1/3 text-center">
						<div class="inline-block p-6 bg-white rounded-xl shadow-sm border border-stone-100">
							<span class="text-xs font-label uppercase text-[var(--color-outline)] block mb-2"><?php _e('Trạng thái', 'laca') ?></span>
							<div class="text-[var(--color-secondary)] font-bold text-lg flex items-center justify-center gap-2">
								<span class="w-2 h-2 rounded-full bg-[var(--color-secondary)] animate-pulse"></span>
								<?php echo esc_html($warrantyLabel); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================================================
			 THE PROCESS
			 ============================================================ -->
	<?php if (!empty($processSteps)): ?>
		<section class="mb-10 md:mb-20">
			<h2 class=" text-3xl font-bold mb-12 tracking-tight"><?php _e('Quy trình thực hiện', 'laca') ?></h2>
			<div class="space-y-4">
				<?php foreach ($processSteps as $idx => $step):
					$isLast = ($idx === count($processSteps) - 1);
					$num = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
					$title = $step['process_title'] ?? '';
					$desc = $step['process_desc'] ?? '';
					?>
					<?php if ($isLast): ?>
						<div class="flex items-center gap-8 p-5 md:p-6 bg-white rounded-lg border-2 border-stone-200">
							<span class=" text-4xl font-extrabold text-stone-200"><?php echo esc_html($num); ?></span>
							<div class="flex-1">
								<h4 class="font-bold"><?php echo esc_html($title); ?></h4>
								<p class="text-sm text-[var(--color-on-surface-variant)]"><?php echo esc_html($desc); ?></p>
							</div>
							<span class="material-symbols-outlined text-[var(--color-primary)]">done_all</span>
						</div>
					<?php else: ?>
						<div class="flex items-center gap-8 p-5 md:p-6 bg-stone-50 rounded-lg border border-stone-200/50">
							<span class=" text-4xl font-extrabold text-stone-200"><?php echo esc_html($num); ?></span>
							<div class="flex-1">
								<h4 class="font-bold"><?php echo esc_html($title); ?></h4>
								<p class="text-sm text-[var(--color-on-surface-variant)]"><?php echo esc_html($desc); ?></p>
							</div>
							<span class="material-symbols-outlined text-[var(--color-outline)]">arrow_forward</span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================================================
			 INVESTMENT DETAILS
			 ============================================================ -->
	<?php if (!empty($quotationItems) || $priceBuild): ?>
		<section class="mb-10 md:mb-20">
			<div class="bg-white rounded-2xl overflow-hidden border border-stone-200">
				<div class="px-5 md:px-8 py-5 md:py-10 bg-[var(--color-surface-container-low)] border-b border-stone-200">
					<h2 class=" text-3xl font-bold tracking-tight"><?php _e('Chi tiết báo giá', 'laca') ?></h2>
				</div>
				<div>
					<table class="w-full text-left border-collapse">
						<thead>
							<tr class="bg-[var(--color-surface-container-low)]/50">
								<th class="px-3 md:px-8 py-5 md:py-4 font-label text-xs uppercase tracking-widest text-[var(--color-outline)]"><?php _e('Mô tả hạng mục', 'laca') ?></th>
								<th class="px-3 md:px-8 py-5 md:py-4 font-label text-xs uppercase tracking-widest text-[var(--color-outline)] text-right"><?php _e('Thành tiền (VND)', 'laca') ?></th>
							</tr>
						</thead>
						<tbody class="divide-y divide-stone-100">
							<?php if (!empty($quotationItems)): ?>
								<?php foreach ($quotationItems as $i => $qi):
									$unitPrice = (int) preg_replace('/[^0-9]/', '', $qi['item_unit_price'] ?? '0');
									$qty = max(1, (int) ($qi['item_qty'] ?? 1));
									$lineTotal = $unitPrice * $qty;
									?>
									<tr>
										<td class="px-3 md:px-8 py-5 md:py-6">
											<p class="font-medium text-stone-900"><?php echo esc_html($qi['item_name'] ?? ''); ?></p>
											<?php if (!empty($qi['item_desc'])): ?>
												<p class="text-xs text-[var(--color-on-surface-variant)] mt-1"><?php echo esc_html($qi['item_desc']); ?></p>
											<?php endif; ?>
										</td>
										<td class="px-3 md:px-8 py-5 md:py-6 text-right  font-bold">
											<?php
											if (!empty($qi['item_note'])) {
												echo '<span class="font-medium italic text-stone-400">' . esc_html($qi['item_note']) . '</span>';
											} elseif ($lineTotal > 0) {
												echo esc_html(number_format($lineTotal, 0, ',', '.')) . ' đ';
											} else {
												echo '<span class="font-medium italic text-stone-400">—</span>';
											}
											?>
										</td>
									</tr>

								<?php endforeach; ?>
							<?php endif; ?>

							<!-- Renewal info row -->
							<?php if ($domainPrice || $hostingPrice): ?>
								<tr class="bg-[var(--color-surface-container-low)]/30">
									<td class="px-3 md:px-8 py-5 md:py-6" colspan="2">
										<h4 class="font-label text-[10px] uppercase tracking-widest text-[var(--color-outline)] mb-4">Thông tin phí gia hạn hằng năm</h4>
										<div class="flex flex-col md:flex-row gap-8">
											<?php if ($domainPrice): ?>
												<div class="flex items-center gap-3">
													<span class="text-xs text-[var(--color-on-surface-variant)]"><?php _e('Gia hạn domain / năm', 'laca') ?>:</span>
													<span class="font-bold text-sm"><?php echo esc_html($domainPrice); ?> đ/năm</span>
												</div>
											<?php endif; ?>
											<?php if ($hostingPrice): ?>
												<div class="flex items-center gap-3">
													<span class="text-xs text-[var(--color-on-surface-variant)]"><?php _e('Gia hạn hosting / năm', 'laca') ?>:</span>
													<span class="font-bold text-sm"><?php echo esc_html($hostingPrice); ?> đ/năm</span>
												</div>
											<?php endif; ?>
										</div>
									</td>
								</tr>
							<?php endif; ?>

							<!-- Total row -->
							<tr class="bg-[var(--color-primary)]/5">
								<td colspan="2" class="px-3 md:px-8 py-5 md:py-6">
									<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-1 text-center md:text-left">
										<p class="text-lg md:text-xl font-bold"><?php _e('Tổng chi phí xây dựng', 'laca') ?></p>
										<p class="text-2xl md:text-3xl font-extrabold text-[var(--color-primary)]"><?php echo esc_html($totalDisplay); ?> đ</p>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================================================
			 PAYMENT PROCESS
			 ============================================================ -->
	<?php if (!empty($paymentSteps)): ?>
		<section class="mb-10 md:mb-20 px-5 md:px-8 py-10 bg-[var(--color-surface-container-low)] rounded-2xl border border-stone-200">
			<h2 class="font-label text-xs uppercase tracking-[0.2em] text-[var(--color-outline)] mb-8 text-center"><?php _e('Quy trình thanh toán', 'laca') ?></h2>
			<div class="flex flex-col md:flex-row items-center justify-between gap-8 md:gap-4">
				<?php foreach ($paymentSteps as $idx => $step):
					$stepNum = $idx + 1;
					$stepTitle = $step['step_title'] ?? '';
					$stepDesc = $step['step_desc'] ?? '';
					$isFirst = ($idx === 0);
					?>
					<?php if ($idx > 0): ?>
						<div class="hidden md:block w-12 h-px bg-stone-300"></div>
					<?php endif; ?>
					<div class="flex-1 flex flex-col items-center text-center">
						<?php if ($isFirst): ?>
							<div class="w-10 h-10 rounded-full bg-[var(--color-primary)] text-white flex items-center justify-center font-bold mb-3 shadow-sm">
								<?php echo esc_html($stepNum); ?>
							</div>
						<?php else: ?>
							<div class="w-10 h-10 rounded-full bg-white border-2 border-[var(--color-primary)]/20 text-[var(--color-primary)] flex items-center justify-center font-bold mb-3">
								<?php echo esc_html($stepNum); ?>
							</div>
						<?php endif; ?>
						<p class="font-bold text-sm mb-1"><?php echo esc_html($stepTitle); ?></p>
						<p class="text-[11px] text-[var(--color-on-surface-variant)]"><?php echo esc_html($stepDesc); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>