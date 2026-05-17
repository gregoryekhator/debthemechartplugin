import pandas as pd
import os

# 1. Load the data you downloaded from Moodle
file_name = 'user_data_mine.csv' # Rename your downloaded file to match this

if not os.path.exists(file_name):
    print(f"Error: {file_name} not found in this folder!")
else:
    df = pd.read_csv(file_name)
    
    # 2. The Logic: Calculate 'Risk'
    # We define 'At Risk' as having a grade below 50 
    # and activity levels below the average of all your courses.
    avg_activity = df['activity_count'].mean()
    
    print("--- Debonair AI Analysis ---")
    print(f"Average User Activity: {avg_activity:.2f} clicks per course")
    print("-" * 30)

    for index, row in df.iterrows():
        risk_status = "STABLE"
        
        # Simple ML logic: Low grades + Low activity = High Risk
        if row['grade'] < 50 and row['activity_count'] < avg_activity:
            risk_status = "HIGH RISK (Trigger Recovery Plan)"
        elif row['grade'] < 60:
            risk_status = "MODERATE RISK"

        print(f"Course ID: {int(row['courseid'])} | Grade: {row['grade']}% | Status: {risk_status}")