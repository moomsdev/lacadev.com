<?php
/**
 * Theme Options.
 *
 * Here, you can register Theme Options using the Carbon Fields library.
 *
 * @link    https://carbonfields.net/docs/containers-theme-options/
 *
 * @package WPEmergeCli
 */

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

$optionsPage = Container::make('theme_options', __('Laca Theme', 'laca'))
	->set_page_file('app-theme-options.php')
	->set_page_menu_position(3)
	->add_tab(__('Branding | Thương hiệu', 'laca'), [
		Field::make('color', 'primary_color', __('Primary color', 'laca'))
			->set_width(33.33),
		Field::make('color', 'secondary_color', __('Secondary color', 'laca'))
			->set_width(33.33),
		Field::make('color', 'bg_color', __('Background color', 'laca'))
			->set_width(33.33),

		Field::make('color', 'primary_color_dark', __('Primary color dark', 'laca'))
			->set_width(33.33),
		Field::make('color', 'secondary_color_dark', __('Secondary color dark', 'laca'))
			->set_width(33.33),
		Field::make('color', 'bg_color_dark', __('Background color dark', 'laca'))
			->set_width(33.33),

		Field::make('image', 'logo', __('Logo', 'laca'))
			->set_width(33.33),
		Field::make('image', 'logo_dark', __('Logo Dark', 'laca'))
			->set_width(33.33),
		Field::make('image', 'default_image', __('Default image | Hình ảnh mặc định', 'laca'))
			->set_width(33.33),
	])

	->add_tab(__('Contact | Liên hệ', 'laca'), [
		Field::make('html', 'info', __('', 'laca'))
			->set_html('----<i> Information | Thông tin </i>----'),
		Field::make('text', 'company' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'Company | Công ty'),
		Field::make('text', 'address' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'Address | Địa chỉ'),
		Field::make('textarea', 'googlemap' . currentLanguage(), __('', 'laca'))
			->set_attribute('placeholder', 'Google map'),
		Field::make('text', 'email' . currentLanguage(), __('', 'laca'))->set_width(33.33)
			->set_attribute('placeholder', 'Email'),
		Field::make('text', 'phone_number' . currentLanguage(), __('', 'laca'))->set_width(33.33)
			->set_attribute('placeholder', 'Phone number | Số điện thoại'),
		Field::make('text', 'hour_working' . currentLanguage(), __('', 'laca'))->set_width(33.33)
			->set_attribute('placeholder', 'Hour working | Giờ làm việc'),
		Field::make('html', 'socials', __('', 'laca'))
			->set_html('----<i> Socials | Mạng xã hội </i>----'),
		Field::make('text', 'facebook' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'facebook'),
		Field::make('text', 'linkedin' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'linkedin'),
		Field::make('text', 'instagram' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'instagram'),
		Field::make('text', 'tiktok' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'tiktok'),
		Field::make('text', 'youtube' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'youtube'),
		Field::make('text', 'zalo' . currentLanguage(), __('', 'laca'))->set_width(50)
			->set_attribute('placeholder', 'zalo'),
	])

	->add_tab(__('Archive pages | List bài viết CPT', 'laca'), [
		Field::make('html', 'service', __('', 'laca'))
			->set_html('----<i> Service </i>----'),
		Field::make('text', 'service_page_title' . currentLanguage(), __('', 'laca'))
			->set_attribute('placeholder', 'Service page title | Tiêu đề trang dịch vụ'),
		Field::make('text', 'service_page_description' . currentLanguage(), __('', 'laca'))
			->set_attribute('placeholder', 'Service page description | Mô tả trang dịch vụ'),

		Field::make('html', 'project', __('', 'laca'))
			->set_html('----<i> Project </i>----'),
		Field::make('text', 'project_page_title' . currentLanguage(), __('', 'laca'))
			->set_attribute('placeholder', 'Project page title | Tiêu đề trang dự án'),
		Field::make('text', 'project_page_description' . currentLanguage(), __('', 'laca'))
			->set_attribute('placeholder', 'Project page description | Mô tả trang dự án'),
	])

	->add_tab(__('Scripts', 'laca'), [
		Field::make('header_scripts', 'crb_header_script', __('Header Script', 'laca')),
		Field::make('footer_scripts', 'crb_footer_script', __('Footer Script', 'laca')),
	]);