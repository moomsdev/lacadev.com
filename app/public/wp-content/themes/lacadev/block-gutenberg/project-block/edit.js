import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	URLInput,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	SelectControl,
	RangeControl,
	CheckboxControl,
	Spinner,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

export default function Edit( { attributes, setAttributes } ) {
	const {
		title,
		description,
		categoryIds,
		orderBy,
		countDesktop,
		countMobile,
		buttonText,
		buttonUrl,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'laca-project-block editor-view',
	} );

	const categories = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'project_cat', {
			per_page: -1,
			hide_empty: true,
		} );
	}, [] );

	const maxCount = Math.max( countDesktop, countMobile );

	const { displayPosts, isLoading } = useSelect(
		( select ) => {
			console.log('--- Project Block Loading ---', { categoryIds, maxCount });
			const { getEntityRecords, isResolving: isResolvingSelector } = select( 'core' );
			const query = {
				per_page: maxCount,
				status: 'publish',
				_embed: true,
			};

			if ( categoryIds && categoryIds.length > 0 ) {
				// FORCE STRING FORMAT
				query.project_cat = categoryIds.map(id => String(id)).join(',');
			}

			const records = getEntityRecords( 'postType', 'project', query );
			const resolving = isResolvingSelector( 'getEntityRecords', [ 'postType', 'project', query ] );
			
			return {
				displayPosts: records || [],
				isLoading: resolving,
			};
		},
		[ maxCount, categoryIds, orderBy ]
	);

	const toggleCategory = ( id ) => {
		const newList = [ ...categoryIds ];
		if ( newList.includes( id ) ) {
			const index = newList.indexOf( id );
			newList.splice( index, 1 );
		} else {
			newList.push( id );
		}
		setAttributes( { categoryIds: newList } );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Cấu hình Project Block', 'laca' ) }>
					<TextControl
						label={ __( 'Tiêu đề block', 'laca' ) }
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
					/>
					<TextareaControl
						label={ __( 'Mô tả block', 'laca' ) }
						value={ description }
						onChange={ ( value ) =>
							setAttributes( { description: value } )
						}
						rows={ 3 }
					/>
					<hr />
					
					<p><strong>{ __( 'Chọn danh mục hiển thị (Tab)', 'laca' ) }</strong></p>
					{ ! categories ? (
						<Spinner />
					) : (
						<div style={ { maxHeight: '200px', overflowY: 'auto', border: '1px solid #ddd', padding: '10px', marginBottom: '15px' } }>
							{ categories.map( ( cat ) => (
								<CheckboxControl
									key={ cat.id }
									label={ cat.name }
									checked={ categoryIds.includes( cat.id ) }
									onChange={ () => toggleCategory( cat.id ) }
								/>
							) ) }
						</div>
					) }

					<SelectControl
						label={ __( 'Sắp xếp theo', 'laca' ) }
						value={ orderBy }
						options={ [
							{ label: __( 'Mới nhất', 'laca' ), value: 'date' },
							{ label: __( 'Ngẫu nhiên', 'laca' ), value: 'rand' },
							{ label: __( 'Dự án thực tế', 'laca' ), value: 'hand_made' },
						] }
						onChange={ ( value ) => setAttributes( { orderBy: value } ) }
					/>

					<RangeControl
						label={ __( 'Số lượng hiển thị (Desktop)', 'laca' ) }
						value={ countDesktop }
						onChange={ ( value ) =>
							setAttributes( { countDesktop: value } )
						}
						min={ 1 }
						max={ 20 }
					/>
                    
					<RangeControl
						label={ __( 'Số lượng hiển thị (Mobile)', 'laca' ) }
						value={ countMobile }
						onChange={ ( value ) =>
							setAttributes( { countMobile: value } )
						}
						min={ 1 }
						max={ 20 }
					/>

					<hr />
					<p>
						<strong>{ __( 'Cấu hình Button:', 'laca' ) }</strong>
					</p>
					<TextControl
						label={ __( 'Nội dung button', 'laca' ) }
						value={ buttonText }
						onChange={ ( value ) =>
							setAttributes( { buttonText: value } )
						}
					/>
					<div style={ { marginBottom: '15px' } }>
						<div style={ { display: 'block', marginBottom: '5px' } }>
							{ __( 'Nhập URL:', 'laca' ) }
						</div>
						<URLInput
							value={ buttonUrl }
							onChange={ ( value ) =>
								setAttributes( { buttonUrl: value } )
							}
						/>
					</div>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="laca-project-block__container container">
					{ ( title || description ) && (
						<div className="laca-project-block__header">
							{ title && (
								<RichText
									tagName="h2"
									className="laca-project-block__title"
									value={ title }
									onChange={ ( value ) =>
										setAttributes( { title: value } )
									}
									placeholder={ __( 'Nhập tiêu đề…', 'laca' ) }
								/>
							) }
							{ description && (
								<RichText
									tagName="div"
									className="laca-project-block__desc"
									value={ description }
									onChange={ ( value ) =>
										setAttributes( { description: value } )
									}
									placeholder={ __( 'Nhập mô tả…', 'laca' ) }
								/>
							) }
						</div>
					) }

					{ categoryIds && categoryIds.length > 0 && (
						<div className="laca-project-block__tabs">
							<div className="tab-item is-active">{ __( 'All', 'laca' ) }</div>
							{ categories && categories.filter(c => categoryIds.includes(c.id)).map(cat => (
								<div key={cat.id} className="tab-item">{cat.name}</div>
							))}
						</div>
					)}

					<div className="laca-project-block__grid">
						{ isLoading ? (
							<div className="laca-editor-placeholder">
								<Spinner />
								<p>{ __( 'Đang tải dự án...', 'laca' ) }</p>
							</div>
						) : (
							<>
								{ displayPosts.length === 0 && (
									<div className="laca-editor-placeholder">
										<p>{ __( 'Không tìm thấy dự án nào. Vui lòng kiểm tra lại danh mục hoặc trạng thái bài viết.', 'laca' ) }</p>
									</div>
								) }
								
								{ displayPosts.map( ( post, index ) => {
									let itemClass = 'laca-project-block__item';
									if ( index + 1 > countDesktop ) {
										itemClass += ' hidden-on-desktop';
									}
									if ( index + 1 > countMobile ) {
										itemClass += ' hidden-on-mobile';
									}

									const quickViewImgId = post.meta?.quick_view_img;

									return (
										<div key={ post.id } className={ itemClass }>
											<div className="laca-project-block__card-link" style={ { pointerEvents: 'none' } }>
												<div className="laca-project-block__image-wrap">
													{ post.featured_media ? (
														<PostImage id={ post.featured_media } />
													) : (
														<div className="laca-project-block__image-placeholder"></div>
													) }
													
													{ quickViewImgId && (
														<div className="laca-project-block__hover-img-wrap">
															<PostImage id={ quickViewImgId } isHover={true} />
														</div>
													) }
												</div>
											</div>
										</div>
									);
								} ) }
							</>
						) }
					</div>

					{ buttonText && (
						<div className="block-footer laca-project-block__footer">
							<div className="btn btn-minimal">
								<span className="btn-icon"></span>
								<span className="btn-text">{ buttonText }</span>
							</div>
						</div>
					) }
				</div>
			</section>
		</>
	);
}

function PostImage( { id, isHover = false } ) {
	const media = useSelect(
		( select ) => {
			// Carbon Fields might return a URL instead of ID if configured that way
			// We check if id is a number
			return isNaN(parseInt(id)) ? null : select( 'core' ).getMedia( id );
		},
		[ id ]
	);

	const className = isHover ? 'laca-project-block__hover-img' : 'laca-project-block__img';

	if ( ! media ) {
		// If id is a URL, use it directly
		if ( typeof id === 'string' && id.startsWith('http') ) {
			return <img src={ id } alt="" className={ className } />;
		}
		return <div className="laca-project-block__image-placeholder"></div>;
	}

	return (
		<img
			src={
				media.media_details?.sizes?.medium?.source_url ||
				media.source_url
			}
			alt={ media.alt_text }
			className={ className }
		/>
	);
}
