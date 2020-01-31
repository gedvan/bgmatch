<template>
  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="57.171" height="53.611"
       viewBox="0 0 15.127 14.185">
    <defs>
      <linearGradient gradientTransform="translate(34.78 53.352)" xlink:href="#a" id="f" x1="80.138" y1="28.701"
                      x2="80.138" y2="42.802" gradientUnits="userSpaceOnUse"/>
      <linearGradient id="a">
        <stop offset="0" stop-opacity=".146"/>
        <stop offset="1" stop-opacity=".317"/>
      </linearGradient>
      <linearGradient xlink:href="#b" id="e" gradientUnits="userSpaceOnUse"
                      gradientTransform="matrix(.96303 0 0 .96303 74.434 63.338)" x1="42.188" y1="30.773" x2="42.188"
                      y2="20.109"/>
      <linearGradient id="b">
        <stop offset="0" stop-color="#fff" stop-opacity=".377"/>
        <stop offset="1" stop-color="#fff" stop-opacity="0"/>
      </linearGradient>
      <linearGradient xlink:href="#c" id="d" gradientUnits="userSpaceOnUse"
                      gradientTransform="matrix(.96705 0 0 .9492 74.265 63.782)" x1="35.079" y1="32.55" x2="48.55"
                      y2="32.55"/>
      <linearGradient id="c">
        <stop offset="0" stop-opacity=".196"/>
        <stop offset=".514" stop-color="#000305" stop-opacity="0"/>
        <stop offset="1" stop-opacity=".196"/>
      </linearGradient>
    </defs>
    <g transform="translate(-107.342 -82.02)" paint-order="markers stroke fill">
      <path
        d="M114.927 82.02c-2.063 0-3.936.63-5.304 1.665-1.3.982-2.124 2.348-2.206 3.865h-.027l-.002.227-.001.007v.016l-.025 2.628v.001c0 1.614.867 3.066 2.236 4.1 1.368 1.034 3.241 1.664 5.304 1.664 2.064 0 3.937-.63 5.305-1.664 1.368-1.034 2.236-2.486 2.236-4.1l.002-2.29c.012-.116.02-.232.023-.35v-.005c0-1.613-.868-3.065-2.236-4.1-1.369-1.033-3.241-1.663-5.304-1.663z"
        :fill="color"/>
      <path
        d="M107.89 87.955l-.024 2.482c0 2.883 3.15 5.22 7.036 5.22 3.886 0 7.037-2.337 7.037-5.22l.002-2.477h-.008a7.01 5.2 0 01-7.007 5.162 7.01 5.2 0 01-7.008-5.167z"
        fill="url(#d)"/>
      <ellipse cx="114.927" cy="87.834" rx="7.007" ry="5.295" fill="url(#e)"/>
      <path
        style="shape-padding:0;isolation:auto;mix-blend-mode:normal;solid-color:#000;solid-opacity:1"
        d="M114.918 82.026c-2.066 0-3.94.622-5.31 1.643-1.319.983-2.17 2.349-2.233 3.87h-.006l-.027 2.885v.002c0 1.617.87 3.074 2.242 4.11 1.372 1.038 3.25 1.67 5.32 1.67 2.068 0 3.944-.632 5.316-1.67 1.373-1.036 2.243-2.493 2.243-4.11l.002-2.643.002-.066-.002-.064v-.108h-.004c-.06-1.524-.913-2.892-2.233-3.876-1.37-1.02-3.245-1.643-5.31-1.643zm0 .523c1.957 0 3.725.595 4.992 1.539 1.214.905 1.962 2.12 2.021 3.457v.342l.001 1.934v.605c0 1.428-.763 2.729-2.031 3.688-1.269.958-3.038 1.56-4.998 1.56-1.96 0-3.732-.602-5-1.56-1.268-.959-2.03-2.26-2.03-3.686v-.002l.005-.653.02-2.056c0-1.406.764-2.685 2.03-3.63 1.267-.943 3.032-1.538 4.99-1.538z"
        color="#000" overflow="visible" fill="url(#f)"/>
    </g>
  </svg>
</template>

<script>
  export default {
    props: ['color'],

    data() {
      return {
      }
    }
  }
</script>
