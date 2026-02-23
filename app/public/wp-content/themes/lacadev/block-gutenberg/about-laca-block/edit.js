import { __ } from '@wordpress/i18n';
import { 
	useBlockProps, 
	RichText, 
	MediaUpload, 
	MediaPlaceholder,
	InspectorControls 
} from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { content, bgImageId, bgImageUrl } = attributes;

	const onSelectImage = ( media ) => {
		setAttributes( {
			bgImageId: media.id,
			bgImageUrl: media.url,
		} );
	};

	const removeImage = () => {
		setAttributes( {
			bgImageId: undefined,
			bgImageUrl: undefined,
		} );
	};

	const blockProps = useBlockProps( {
		className: 'block-about-laca editor-view',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Cấu hình Ảnh nền', 'laca' ) }>
					{ bgImageUrl && (
						<div className="image-preview" style={{ marginBottom: '10px' }}>
							<img src={ bgImageUrl } alt="" style={{ maxWidth: '100%', height: 'auto' }} />
							<Button isDestructive onClick={ removeImage }>
								{ __( 'Xóa ảnh', 'laca' ) }
							</Button>
						</div>
					) }
					<MediaUpload
						onSelect={ onSelectImage }
						allowedTypes={ [ 'image' ] }
						value={ bgImageId }
						render={ ( { open } ) => (
							<Button isPrimary onClick={ open }>
								{ ! bgImageId ? __( 'Chọn ảnh nền', 'laca' ) : __( 'Thay đổi ảnh', 'laca' ) }
							</Button>
						) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Nội dung', 'laca' ) }>
					<RichText
						tagName="div"
						className="about-content"
						value={ content }
						onChange={ ( value ) => setAttributes( { content: value } ) }
						placeholder={ __( 'Nhập nội dung giới thiệu...', 'laca' ) }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="img-container">
					{ ! bgImageUrl ? (
						<MediaPlaceholder
							icon="format-image"
							labels={ { title: __( 'Chọn ảnh nền', 'laca' ) } }
							onSelect={ onSelectImage }
							accept="image/*"
							allowedTypes={ [ 'image' ] }
						/>
					) : (
						<div 
							className="bg-image-preview" 
							style={ { backgroundImage: `url(${ bgImageUrl })` } }
						></div>
					) }
					
					<div className="content-wrapper">
						<RichText
							tagName="div"
							className="about-content"
							value={ content }
							onChange={ ( value ) => setAttributes( { content: value } ) }
							placeholder={ __( 'Nhập nội dung giới thiệu...', 'laca' ) }
						/>
					</div>
				</div>
			</section>
		</>
	);
}
