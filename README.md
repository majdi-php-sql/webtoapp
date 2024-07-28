
<h1>GitHub Actions Workflow for Building APK</h1>

<p>This GitHub Actions workflow automates the process of building an APK from your Android project. It sets up the necessary environment, builds the APK, and handles errors effectively to provide useful debugging information.</p>

<h2>Features</h2>
<ul>
    <li>Checkout code from the repository.</li>
    <li>Set up JDK 11 using Temurin distribution.</li>
    <li>Install Android SDK with specified API level and build tools version.</li>
    <li>Run the Gradle build process with detailed logging and stack traces.</li>
    <li>Upload the built APK as an artifact if the build is successful.</li>
    <li>Print build logs and directory contents for debugging in case of failure.</li>
</ul>

<h2>Requirements</h2>
<ul>
    <li>A GitHub repository containing an Android project with a Gradle build script.</li>
    <li>GitHub Actions enabled for the repository.</li>
    <li>A compatible version of Node.js for running GitHub Actions (Node.js 20 recommended).</li>
</ul>

<h2>Instructions</h2>
<ol>
    <li>Create a new file in your repository named <code>.github/workflows/build.yml</code>.</li>
    <li>Copy and paste the following workflow code into the <code>build.yml</code> file:</li>
</ol>

<pre><code>
name: Build APK

on:
  push:
    branches:
      - main
  pull_request:
    branches:
      - main
  repository_dispatch:
    types: [build_apk]

jobs:
  build:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v3

    - name: Set up JDK 11
      uses: actions/setup-java@v3
      with:
        java-version: '11'
        distribution: 'temurin'

    - name: Make gradlew executable
      run: chmod +x gradlew

    - name: Install Android SDK
      uses: android-actions/setup-android@v2
      with:
        api-level: 30
        build-tools: '30.0.3'

    - name: Build with Gradle
      run: ./gradlew build --info --stacktrace
      env:
        JAVA_HOME: ${{ steps.setup-java.outputs.path }}
      continue-on-error: true

    - name: Upload APK
      if: success()
      uses: actions/upload-artifact@v3
      with:
        name: apk
        path: app/build/outputs/apk/release/app-release.apk

    - name: Print Gradle Build Logs
      if: failure()
      run: cat /home/runner/work/_temp/_runner_file_*/gradle_output.txt || echo 'No Gradle output log found'

    - name: Print Build Directory
      if: failure()
      run: ls -la app/build/outputs/apk/release

    - name: Fail the build on error
      if: failure()
      run: exit 1
</code></pre>

<ol start="3">
    <li>Commit and push the <code>build.yml</code> file to your repository.</li>
    <li>On every push to the main branch or on a pull request to the main branch, the workflow will trigger and start the build process.</li>
    <li>If the build is successful, the APK will be uploaded as an artifact. If the build fails, logs and directory contents will be printed for debugging.</li>
</ol>

<h2>Debugging and Error Handling</h2>
<p>The workflow includes several steps to aid in debugging:</p>
<ul>
    <li><strong>Conditional Steps:</strong> The <code>Upload APK</code> step runs only if the build succeeds. The <code>Print Gradle Build Logs</code> and <code>Print Build Directory</code> steps run only if the build fails.</li>
    <li><strong>Continue on Error:</strong> The Gradle build step is set to continue on error to ensure subsequent debugging steps are executed.</li>
    <li><strong>Explicit Failure:</strong> The final step explicitly fails the build if any previous step failed, ensuring a clear failure status.</li>
</ul>

<p>By following these instructions and using the provided workflow, you can automate the APK build process and efficiently handle errors to facilitate debugging.</p>
