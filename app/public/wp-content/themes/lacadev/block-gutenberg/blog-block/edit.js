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
		className: 'block-blog editor-view',
	} );

	// Fetch categories
	const categories = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
			per_page: -1,
			hide_empty: true,
		} );
	}, [] );

	// Fetch posts based on mode
	const displayPosts = useSelect(
		( select ) => {
			if ( mode === 'manual' && ( ! postIds || postIds.length === 0 ) ) {
				return [];
			}

			const { getEntityRecords } = select( 'core' );
			const query = {
				per_page: mode === 'manual' ? -1 : count,
				status: 'publish',
				_embed: true, // For featured media
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
				<PanelBody title={ __( 'Cấu hình Blog', 'laca' ) }>
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
										label: __(
											'Nhiều bình luận nhất (Proxy Popularity)',
											'laca'
										),
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
								max={ 12 }
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
							{ ! displayPosts ? (
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
									{ displayPosts.map( ( post ) => (
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
						style={ { textAlign: 'center', marginBottom: '40px' } }
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

					<div className="blog-list">
						{ ! displayPosts && <Spinner /> }
						{ displayPosts &&
							displayPosts.length > 0 &&
							displayPosts.map( ( post ) => (
								<div key={ post.id } className="blog-card">
									<div
										className="card-link"
										style={ { pointerEvents: 'none' } }
									>
										<div className="card-image-wrap">
											{ post.featured_media ? (
												<PostImage
													id={ post.featured_media }
												/>
											) : (
												<div
													style={ {
														width: '100%',
														height: '220px',
														background: '#f0f0f0',
														borderRadius: '20px',
													} }
												></div>
											) }
										</div>
										<div className="card-body">
											<h3 className="card-title">
												{ decodeEntities(
													post.title.rendered
												) }
											</h3>
											<div className="card-meta">
												<span>
													{ __(
														'By Author',
														'laca'
													) }
												</span>
												<span>
													{ __( 'Just now', 'laca' ) }
												</span>
											</div>
										</div>
									</div>
								</div>
							) ) }
					</div>

					{ buttonText && (
						<div className="block-footer">
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

function PostImage( { id } ) {
	const media = useSelect(
		( select ) => select( 'core' ).getMedia( id ),
		[ id ]
	);
	if ( ! media ) {
		return <div className="card-image-placeholder"></div>;
	}
	return (
		<img
			src={
				media.media_details?.sizes?.medium?.source_url ||
				media.source_url
			}
			alt={ media.alt_text }
		/>
	);
}
