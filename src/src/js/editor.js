import classnames from 'classnames';

import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';

const addClassnames = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( 'core/embed' !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const newProps = { ...props };
		newProps.className = classnames( {
			[ newProps?.className ]: !! newProps?.className,
			[ `is-provider-${ props.attributes.providerNameSlug }` ]:
				!! props.attributes?.providerNameSlug,
		} );

		return <BlockEdit { ...newProps } />;
	};
}, 'addClassnames' );

addFilter(
	'editor.BlockEdit',
	'wp-oembed-blog-card/add-classnames',
	addClassnames
);
