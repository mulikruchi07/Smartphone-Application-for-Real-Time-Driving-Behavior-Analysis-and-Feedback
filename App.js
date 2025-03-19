import React, { useEffect, useRef, useState } from 'react';
import { WebView } from 'react-native-webview';
import { BackHandler, StyleSheet } from 'react-native';
import { accelerometer, gyroscope } from 'react-native-sensors';


const WebViewScreen = () => {
  const webViewRef = useRef(null);
  const [currentUrl, setCurrentUrl] = useState('');
  const [speed, setSpeed] = useState(0);
  const [cornering, setCornering] = useState(0);
  const [braking, setBraking] = useState(0);
  const [calibration, setCalibration] = useState({ x: 0, y: 0, z: 0 });

  const NOISE_THRESHOLD = 1.0; // Ignore small fluctuations
  const MOVING_AVERAGE_COUNT = 20;
  let accBuffer = [];
  let gyroBuffer = [];

  // Handle Back Button
  const handleBackPress = () => {
    if (currentUrl.endsWith('owner_profile.php' || 'driver_profile.php')) {
     BackHandler.exitApp();
     return true;
   } else if (webViewRef.current) {
     webViewRef.current.goBack();
     return true;
   }
   return false;
 };

 useEffect(() => {
   const backHandler = BackHandler.addEventListener('hardwareBackPress', handleBackPress);
   return () => backHandler.remove();
 }, [currentUrl]);

  // Calibration - Get average of 100 readings
  useEffect(() => {
    let calibrationReadings = [];
    const calibrationSubscription = accelerometer.subscribe(({ x, y, z }) => {
      calibrationReadings.push({ x, y, z });

      if (calibrationReadings.length >= 100) {
        const avgX = calibrationReadings.reduce((sum, val) => sum + val.x, 0) / calibrationReadings.length;
        const avgY = calibrationReadings.reduce((sum, val) => sum + val.y, 0) / calibrationReadings.length;
        const avgZ = calibrationReadings.reduce((sum, val) => sum + val.z, 0) / calibrationReadings.length;

        setCalibration({ x: avgX, y: avgY, z: avgZ });
        calibrationSubscription.unsubscribe();
      }
    });

    return () => calibrationSubscription.unsubscribe();
  }, []);

  // Moving Average Filter Function
  const movingAverage = (buffer, newValue) => {
    buffer.push(newValue);
    if (buffer.length > MOVING_AVERAGE_COUNT) buffer.shift();
    return buffer.reduce((sum, val) => sum + val, 0) / buffer.length;
  };

  useEffect(() => {
    const accSubscription = accelerometer.subscribe(({ x, y, z }) => {
      const adjustedX = (x - calibration.x)*4;
      const adjustedY = (y - calibration.y)*4;
      const adjustedZ = (z - calibration.z)*4;

      const filteredX = movingAverage(accBuffer, adjustedX);
      const filteredY = movingAverage(accBuffer, adjustedY);
      const filteredZ = movingAverage(accBuffer, adjustedZ);

      const finalX = Math.abs(filteredX) > NOISE_THRESHOLD ? filteredX : 0;
      const finalY = Math.abs(filteredY) > NOISE_THRESHOLD ? filteredY : 0;
      const finalZ = Math.abs(filteredZ) > NOISE_THRESHOLD ? filteredZ : 0;

      setSpeed(Math.sqrt(finalX ** 2 + finalY ** 2 + finalZ ** 2).toFixed(2));
      setBraking(Math.abs(finalZ).toFixed(2));
    });

    const gyroSubscription = gyroscope.subscribe(({ x, y, z }) => {
      const smoothGyro = movingAverage(gyroBuffer, Math.abs(x) + Math.abs(y) + Math.abs(z));
      setCornering(smoothGyro.toFixed(2));
    });

    return () => {
      accSubscription.unsubscribe();
      gyroSubscription.unsubscribe();
    };
  }, [calibration]);

  const calculateScore = () => {
    const w1 = 10; // Weight for Speed
    const w2 = 10; // Weight for Cornering
    const w3 = 10; // Weight for Braking
  
    return Math.max(0, Math.min(100, Math.round(100 - (w1 * speed) - (w2 * cornering) - (w3 * braking))));
  };
  
  useEffect(() => {
    if (currentUrl.includes('dashboard.html') && webViewRef.current) {
      // Function to update score every 10 seconds
      const interval = setInterval(() => {
        const score = calculateScore(); // Calculate the score every 10 sec
  
        const script = `
          (function updateDashboard() {
            const score = ${score};
            document.getElementById('speedText').innerText = score + '%';

            let color;
            if (score < 25) color = 'red';
            else if (score < 50) color = 'yello';
            else if (score < 75) color = 'orange';
            else color = 'green';

            document.getElementById('scoreBox').style.background = 
              'conic-gradient(' + color + ' ' + score + '%, #26374D ' + score + '%)';
         })();
        `;
  
        webViewRef.current.injectJavaScript(script);
      }, 5000); // Runs every 5 seconds
  
      return () => clearInterval(interval); // Cleanup interval on unmount
    }
  }, [currentUrl]); // Run only when the page changes
  
  // Effect to update speed, cornering, and braking continuously
  useEffect(() => {
    if (currentUrl.includes('dashboard.html') && webViewRef.current) {
      const script = `
        (function updateRealTimeData() {
          const speed = ${speed};
          const cornering = ${cornering};
          const braking = ${braking};
  
          document.getElementById('speedBox').innerText = + speed + '%';
          document.getElementById('corneringBox').innerText = 'Cornering: ' + cornering + '%';
          document.getElementById('brakingBox').innerText = 'Braking: ' + braking + '%';
  
          const updateBoxColor = (boxId, value) => {
            let color;
            if (value < 25) color = 'green';
            else if (value < 50) color = 'orange';
            else if (value < 75) color = 'yellow';
            else color = 'red';
  
            document.getElementById(boxId).style.background =
              'linear-gradient(to top, ' + color + ' ' + value + '%, #26374D ' + value + '%)';
          };
  
          updateBoxColor('speedBox', speed);
          updateBoxColor('corneringBox', cornering);
          updateBoxColor('brakingBox', braking);

          // Updating the Dashboard Page
                document.getElementById('speedBox').innerText = 'Speed: ' + speed + '%';
                document.getElementById('corneringBox').innerText = 'Cornering: ' + cornering + '%';
                document.getElementById('brakingBox').innerText = 'Braking: ' + braking + '%';
                
                // Storing values for the report page
                localStorage.setItem('speed', speed);
                localStorage.setItem('cornering', cornering);
                localStorage.setItem('braking', braking);
        })();
      `;
      webViewRef.current.injectJavaScript(script);
    }
  }, [speed, cornering, braking, currentUrl]);


  useEffect(() => {
    BackHandler.addEventListener('hardwareBackPress', handleBackPress);
    return () => {
      BackHandler.removeEventListener('hardwareBackPress', handleBackPress);
    };
  }, []);

  return (
    <WebView
      ref={webViewRef}
      originWhitelist={['*']}
      source={{ uri: 'http://192.168.43.14/anim.html' }} // Replace with your server URL
      style={styles.webview}
      javaScriptEnabled={true}
      geolocationEnabled={true}
      domStorageEnabled={true}
      mixedContentMode="always"
      onNavigationStateChange={(navState) => setCurrentUrl(navState.url)}
    />
  );
};

const styles = StyleSheet.create({
  webview: {
    flex: 1,
  },
});

export default WebViewScreen;