// $KYAULabs: commitlint.config.js Sean Bruen@NOVA 2026/07/04 -0700 Exp $

module.exports = {
	extends: ['@commitlint/config-conventional'],
	rules: {
		'header-max-length': [2, 'always', 100],
		'type-enum': [2, 'always', [
			'build',
			'chore',
			'ci',
			'docs',
			'feat',
			'fix',
			'patch',
			'perf',
			'refactor',
			'revert',
			'style',
			'test',
			'ignore',
		]],
		'trailer-exists': [2, 'always', 'Acked-by:'],
		'signed-off-by': [2, 'always', 'Signed-off-by:'],
	},
};

// vim: ft=javascript sts=4 sw=4 ts=4 noet :
