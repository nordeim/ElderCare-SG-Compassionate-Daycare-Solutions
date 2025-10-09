import { resolve } from 'node:path'
import type { StorybookConfig } from '@storybook/react-vite'

const config: StorybookConfig = {
  stories: ['../src/stories/**/*.stories.@(ts|tsx)'],
  addons: ['@storybook/addon-essentials', '@storybook/addon-interactions', '@storybook/addon-a11y'],
  framework: {
    name: '@storybook/react-vite',
    options: {}
  },
  typescript: {
    check: false,
    reactDocgen: 'react-docgen-typescript',
    reactDocgenTypescriptOptions: {
      shouldExtractLiteralValuesFromEnum: true,
      shouldRemoveUndefinedFromOptional: true,
      propFilter: (prop) => (prop.parent ? !/node_modules/.test(prop.parent.fileName) : true)
    }
  },
  docs: {
    autodocs: 'tag'
  },
  staticDirs: ['../public'],
  viteFinal: async (config, { configType }) => {
    return {
      ...config,
      define: {
        ...config.define,
        ...(configType === 'PRODUCTION' ? { 'process.env': {} } : {})
      },
      esbuild: {
        ...config.esbuild,
        jsx: 'automatic'
      },
      resolve: {
        ...config.resolve,
        alias: {
          ...config.resolve?.alias,
          '@': resolve(process.cwd(), 'src'),
          react: require.resolve('react'),
          'react-dom': require.resolve('react-dom')
        }
      }
    }
  }
}

export default config
