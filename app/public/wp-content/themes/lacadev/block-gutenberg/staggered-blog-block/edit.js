import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
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
	CheckboxControl,
	RangeControl,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

export default function Edit( { attributes, setAttributes } ) {
	const {
		title,
		description,
		mode,
		orderBy,
		categoryIds,
		postIds,
		count,
		buttonText,
		buttonUrl,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'block-staggered-blog editor-view',
	} );

	// Fetch categories
	const categories = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
			per_page: -1,
			hide_empty: true,
		} );
	}, [] );

	// Fetch posts
	const posts = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'postType', 'post', {
			per_page: 20,
			status: 'publish',
		} );
	}, [] );

	// Fetch posts based on attributes
	const displayPosts = useSelect(
		( select ) => {
			if ( mode === 'manual' && ( ! postIds || postIds.length === 0 ) ) {
				return [];
			}

			const { getEntityRecords } = select( 'core' );
			const query = {
				per_page: mode === 'manual' ? -1 : count,
				status: 'publish',
			};

			if ( mode === 'manual' ) {
				query.include = postIds;
				query.orderby = 'include';
			} else {
				if ( categoryIds && categoryIds.length > 0 ) {
					query.categories = categoryIds;
				}
				query.orderby = orderBy === 'rand' ? 'date' : orderBy;
			}

			return getEntityRecords( 'postType', 'post', query );
		},
		[ mode, postIds, count, orderBy, categoryIds ]
	);

	const toggleItem = ( list, id ) => {
		const newList = [ ...list ];
		if ( newList.includes( id ) ) {
			const index = newList.indexOf( id );
			newList.splice( index, 1 );
		} else {
			newList.push( id );
		}
		return newList;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Cấu hình Staggered Blog', 'laca' ) }>
					<TextControl
						label={ __( 'Tiêu đề block (Tùy chọn)', 'laca' ) }
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
					/>
					<TextareaControl
						label={ __( 'Mô tả block (Tùy chọn)', 'laca' ) }
						value={ description }
						onChange={ ( value ) =>
							setAttributes( { description: value } )
						}
						rows={ 2 }
					/>
					<hr />
					<SelectControl
						label={ __( 'Chế độ hiển thị', 'laca' ) }
						value={ mode }
						options={ [
							{
								label: __( 'Tự động (Auto)', 'laca' ),
								value: 'auto',
							},
							{
								label: __( 'Thủ công (Manual)', 'laca' ),
								value: 'manual',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { mode: value } )
						}
					/>

					{ mode === 'auto' && (
						<>
							<p>
								<strong>
									{ __( 'Lọc theo chuyên mục:', 'laca' ) }
								</strong>
							</p>
							{ ! categories ? (
								<Spinner />
							) : (
								<div
									style={ {
										maxHeight: '150px',
										overflowY: 'auto',
										border: '1px solid #ddd',
										padding: '10px',
										marginBottom: '15px',
									} }
								>
									{ categories.map( ( cat ) => (
										<CheckboxControl
											key={ cat.id }
											label={ cat.name }
											checked={ categoryIds.includes(
												cat.id
											) }
											onChange={ () =>
												setAttributes( {
													categoryIds: toggleItem(
														categoryIds,
														cat.id
													),
												} )
											}
										/>
									) ) }
								</div>
							) }

							<SelectControl
								label={ __( 'Sắp xếp theo', 'laca' ) }
								value={ orderBy }
								options={ [
									{
										label: __( 'Mới nhất', 'laca' ),
										value: 'date',
									},
									{
										label: __( 'Ngẫu nhiên', 'laca' ),
										value: 'rand',
									},
									{
										label: __( 'Phổ biến nhất', 'laca' ),
										value: 'comment_count',
									},
								] }
								onChange={ ( value ) =>
									setAttributes( { orderBy: value } )
								}
							/>
							<RangeControl
								label={ __( 'Số lượng bài viết', 'laca' ) }
								value={ count }
								onChange={ ( value ) =>
									setAttributes( { count: value } )
								}
								min={ 1 }
								max={ 10 }
							/>
						</>
					) }

					{ mode === 'manual' && (
						<div className="manual-selection">
							<p>
								<strong>
									{ __( 'Chọn bài viết hiển thị:', 'laca' ) }
								</strong>
							</p>
							{ ! posts ? (
								<Spinner />
							) : (
								<div
									style={ {
										maxHeight: '300px',
										overflowY: 'auto',
										border: '1px solid #ddd',
										padding: '10px',
									} }
								>
									{ posts.map( ( post ) => (
										<CheckboxControl
											key={ post.id }
											label={ post.title.rendered }
											checked={ postIds.includes(
												post.id
											) }
											onChange={ () =>
												setAttributes( {
													postIds: toggleItem(
														postIds,
														post.id
													),
												} )
											}
										/>
									) ) }
								</div>
							) }
						</div>
					) }
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
						<div
							style={ { display: 'block', marginBottom: '5px' } }
						>
							{ __(
								'Nhập URL (chọn page hoặc nhập thủ công):',
								'laca'
							) }
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
				<div className="container">
					<div
						className="block-header"
						style={ { textAlign: 'center', marginBottom: '60px' } }
					>
						<RichText
							tagName="h2"
							className="block-title"
							value={ title }
							onChange={ ( value ) =>
								setAttributes( { title: value } )
							}
							placeholder={ __( 'Nhập tiêu đề…', 'laca' ) }
						/>
						<RichText
							tagName="div"
							className="block-desc"
							value={ description }
							onChange={ ( value ) =>
								setAttributes( { description: value } )
							}
							placeholder={ __( 'Nhập mô tả…', 'laca' ) }
						/>
					</div>

					<div className="staggered-list">
						{ ! displayPosts && <Spinner /> }
						{ displayPosts && displayPosts.length === 0 && (
							<Placeholder
								label={ __( 'Không có bài viết', 'laca' ) }
							>
								{ __(
									'Vui lòng chọn bài viết hoặc kiểm tra cấu hình.',
									'laca'
								) }
							</Placeholder>
						) }
						{ displayPosts &&
							displayPosts.length > 0 &&
							displayPosts.map( ( post, index ) => (
								<div
									key={ post.id }
									className={ `staggered-item ${
										index % 2 !== 0
											? 'staggered-item--even'
											: 'staggered-item--odd'
									}` }
								>
									<div className="staggered-item__content">
										<h3 className="staggered-item__title">
											{ decodeEntities(
												post.title.rendered
											) }
										</h3>
										<div className="staggered-item__desc">
											{ decodeEntities(
												post.excerpt.rendered
													.replace( /<[^>]*>?/gm, '' )
													.substring( 0, 150 )
											) }
											...
										</div>
									</div>
									<div className="staggered-item__image">
										{ post.featured_media ? (
											<PostImage
												id={ post.featured_media }
											/>
										) : (
											<div className="image-placeholder"></div>
										) }
									</div>
								</div>
							) ) }
					</div>

					{ buttonText && (
						<div
							className="block-footer"
							style={ { marginTop: '5rem', textAlign: 'center' } }
						>
							<div
								className="btn btn-minimal"
								style={ { display: 'inline-flex' } }
							>
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

function PostImage( { id } ) {
	const media = useSelect(
		( select ) => select( 'core' ).getMedia( id ),
		[ id ]
	);
	if ( ! media ) {
		return <div className="image-placeholder"></div>;
	}
	return (
		<img
			src={
				media.media_details?.sizes?.large?.source_url ||
				media.source_url
			}
			alt={ media.alt_text }
		/>
	);
}
