export const RAW_PROJECT_FILES = import.meta.glob(
  [
    '/index.html',
    '/vite.config.ts',
    '/tsconfig.json',
    '/package.json',
    '/.env.example',
    '/.gitignore',
    '/metadata.json',
    '/MERGE_GUIDE.md',
    '/INSTALL_WINDOWS.bat',
    '/RUN_WINDOWS.bat',
    '/BUILD_WINDOWS.bat',
    '/README_WINDOWS.txt',
    '/laravel/**/*',
    '/src/**/*.ts',
    '/src/**/*.tsx',
    '/src/**/*.css',
    '!/src/utils/rawProjectFiles.ts',
  ],
  { query: '?raw', import: 'default', eager: true }
) as Record<string, string>;
