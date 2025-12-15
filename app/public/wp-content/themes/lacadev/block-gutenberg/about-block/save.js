import { RichText } from '@wordpress/block-editor';

export default function Save({ attributes }) {
	const { blockID, welcomeContent, aboutImage, aboutTitle, aboutDesc } = attributes;

	return (
		<section className="block-about" id={blockID}>
			<div className="block-about__head">
				<div className="scroll-circle">
					<svg viewBox="0 0 200 200">
						<path
							id="circlePath"
							d="M100,100 m-75,0 a75,75 0 1,1 150,0 a75,75 0 1,1 -150,0"
							fill="none"
						/>
						<text>
							<textPath href="#circlePath" startOffset="0">
								{welcomeContent}
							</textPath>
						</text>
					</svg>
					<div className="arrow"></div>
				</div>

				{aboutImage.url && (
					<div className="block-about__img">
						<figure>
							<img src={aboutImage.url} alt={aboutImage.alt || aboutTitle} loading="lazy" />
						</figure>
					</div>
				)}
			</div>

			<div className="block-about__body">
				<RichText.Content tagName="h2" className="block-title text-center" value={aboutTitle} />
				<RichText.Content tagName="div" className="block-desc" value={aboutDesc} />
			</div>
		</section>
	);
}
